<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobApplicationRequest;
use App\Models\JobApplication;
use App\Services\CvParsingService;
use App\Services\GoogleSheetService;
use App\Services\WebhookService;
use App\Jobs\SendFollowUpEmail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JobApplicationController extends Controller
{
    protected $cvParsingService;
    protected $googleSheetService;
    protected $webhookService;

    public function __construct(
        CvParsingService $cvParsingService,
        GoogleSheetService $googleSheetService,
        WebhookService $webhookService
    ) {
        $this->cvParsingService = $cvParsingService;
        $this->googleSheetService = $googleSheetService;
        $this->webhookService = $webhookService;
    }

 
    public function showForm()
    {
        return view('job-application.form');
    }

    public function store(JobApplicationRequest $request)
    {
        try {
            // 1. Store the file in cloud storage
            $cvFile = $request->file('cv');
            $fileName = time() . '_' . Str::slug($request->name) . '.' . $cvFile->getClientOriginalExtension();
            $filePath = $cvFile->storeAs('cvs', $fileName, 's3');
            
            // 2. Generate public URL for the CV
            $cvPublicUrl = Storage::disk('s3')->url($filePath);
            
            // 3. Create job application record
            $application = JobApplication::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'cv_path' => $filePath,
                'cv_url' => $cvPublicUrl,
                'status' => 'submitted',
            ]);
            
            // 4. Extract CV data
            $cvData = $this->cvParsingService->parseCV($cvFile);
            
            // Debug the cvData to check if it's being parsed correctly
            dd($cvData);  // This will dump and die, showing the contents of $cvData
            
            // 5. Store extracted CV data
            $application->update([
                'parsed_data' => json_encode($cvData),
                'status' => 'processed',
            ]);
            
            // 6. Add data to Google Sheet
            $this->googleSheetService->addRow([
                $application->id,
                $application->name,
                $application->email,
                $application->phone,
                $application->cv_url,
                json_encode($cvData['education']),
                json_encode($cvData['qualifications']),
                json_encode($cvData['projects']),
                json_encode($cvData['personal_info']),
                Carbon::now()->toIso8601String(),
            ]);
            
            // 7. Send webhook notification
            $payload = [
                'cv_data' => $cvData,
                'metadata' => [
                    'applicant_name' => $application->name,
                    'email' => $application->email,
                    'status' => 'prod', 
                    'cv_processed' => true,
                    'processed_timestamp' => Carbon::now()->toIso8601String(),
                ]
            ];
            
            $this->webhookService->send($payload, $request->email);
            
            //  follow-up email for next day
            $userTimezone = $request->timezone ?? 'UTC';
            SendFollowUpEmail::dispatch($application)
                ->delay($this->calculateEmailTime(timezone: $userTimezone));
            
            return redirect()
                ->route('job-application.success')
                ->with('success', 'Your application has been submitted successfully.');
        } catch (\Exception $e) {
            report($e);
            return back()
                ->withInput()
                ->with('error', 'There was an error processing your application. Please try again.');
        }
    }
    
    
    /**
     * Show success page
     */
    public function success()
    {
        return view('job-application.success');
    }
    
    /**
     * to calculate the appropriate time to send the follow-up email
     */
    private function calculateEmailTime($timezone)
    {
        $userTime = Carbon::now($timezone)->addDay()->setHour(9)->setMinute(0)->setSecond(0);
        
        // Convert to server's timezone for scheduling
        return $userTime->setTimezone(config('app.timezone'));
    }
}