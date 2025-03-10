<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory as WordParser;
use Illuminate\Support\Str;

class CvParsingService
{
    /**
     * Parse CV content from uploaded file
     */
    public function parseCV(UploadedFile $file)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $content = '';
        
        // Parse PDF or DOCX file content
        if ($extension === 'pdf') {
            $content = $this->parsePdf($file);
        } elseif ($extension === 'docx') {
            $content = $this->parseDocx($file);
        }
        
        // Extract sections from the content
        return $this->extractSections($content);
    }
    
    /**
     * Parse PDF file content
     */
    private function parsePdf(UploadedFile $file)
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($file->getPathname());
        return $pdf->getText();
    }
    
    /**
     * Parse DOCX file content
     */
    private function parseDocx(UploadedFile $file)
    {
        $phpWord = WordParser::load($file->getPathname());
        $text = '';
        
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . "\n";
                } elseif (method_exists($element, 'getElements')) {
                    foreach ($element->getElements() as $childElement) {
                        if (method_exists($childElement, 'getText')) {
                            $text .= $childElement->getText() . "\n";
                        }
                    }
                }
            }
        }
        
        return $text;
    }
    
    /**
     * Extract sections from CV content
     */
    private function extractSections($content)
    {
        return [
            'personal_info' => $this->extractPersonalInfo($content),
            'education' => $this->extractEducation($content),
            'qualifications' => $this->extractQualifications($content),
            'projects' => $this->extractProjects($content),
        ];
    }
    
    /**
     * Extract personal information
     */
    private function extractPersonalInfo($content)
    {
        $info = [];
        
        // Extract name (first and last)
        preg_match('/([A-Z][a-z]+ [A-Z][a-z]+)/', $content, $nameMatches);
        $info['name'] = $nameMatches[0] ?? '';
        
        // Extract email address
        preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $content, $emailMatches);
        $info['email'] = $emailMatches[0] ?? '';
        
        // Extract phone number
        preg_match('/\+?[0-9]{1,4}?[-.\s]?\(?[0-9]{2,3}?\)?[-.\s]?[0-9]{3}[-.\s]?[0-9]{4,6}/', $content, $phoneMatches);
        $info['phone'] = $phoneMatches[0] ?? '';
        
        return $info;
    }
    
    /**
     * Extract education information
     */
    private function extractEducation($content)
    {
        $education = [];
        $lines = explode("\n", $content);
        
        $inEducationSection = false;
        $currentEntry = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) {
                continue;
            }
            
            // Detect education section
            if (!$inEducationSection && preg_match('/(education|academic background)/i', $line)) {
                $inEducationSection = true;
                continue;
            }
            
            // Detect end of education section
            if ($inEducationSection && preg_match('/(experience|skills|projects|qualifications|awards)/i', $line)) {
                $inEducationSection = false;
                continue;
            }
            
            // Capture degrees and academic details
            if ($inEducationSection && preg_match('/(bachelor|master|phd|degree|bs|ba|ms|ma)/i', $line)) {
                if (!empty($currentEntry)) {
                    $education[] = $currentEntry;
                    $currentEntry = [];
                }
                $currentEntry = ['degree' => $line];
            } elseif ($inEducationSection && !empty($currentEntry)) {
                // Add details to the current education entry
                $currentEntry['details'] = $currentEntry['details'] ?? '';
                $currentEntry['details'] .= ' ' . $line;
            }
        }
        
        if (!empty($currentEntry)) {
            $education[] = $currentEntry;
        }
        
        return $education;
    }
    
    /**
     * Extract qualifications
     */
    private function extractQualifications($content)
    {
        $qualifications = [];
        $lines = explode("\n", $content);
        
        $inQualificationsSection = false;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) {
                continue;
            }
            
            // Detect qualifications section
            if (!$inQualificationsSection && preg_match('/(qualifications|certifications|skills)/i', $line)) {
                $inQualificationsSection = true;
                continue;
            }
            
            // Detect end of qualifications section
            if ($inQualificationsSection && preg_match('/(education|experience|projects|employment)/i', $line)) {
                $inQualificationsSection = false;
                continue;
            }
            
            // Capture valid qualification details
            if ($inQualificationsSection && strlen($line) > 5) {
                $qualifications[] = $line;
            }
        }
        
        return $qualifications;
    }
    
    /**
     * Extract projects
     */
    private function extractProjects($content)
    {
        $projects = [];
        $lines = explode("\n", $content);
        
        $inProjectsSection = false;
        $currentProject = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) {
                continue;
            }
            
            // Detect start of projects section
            if (!$inProjectsSection && preg_match('/(projects|portfolio)/i', $line)) {
                $inProjectsSection = true;
                continue;
            }
            
            // Detect end of projects section
            if ($inProjectsSection && preg_match('/(education|experience|skills|qualifications|employment)/i', $line)) {
                $inProjectsSection = false;
                continue;
            }
            
            // Detect new project (capitalized title or bullet points)
            if ($inProjectsSection && (preg_match('/^[A-Z][A-Za-z\s]+:|•|\*|-/', $line) || Str::length($line) < 50)) {
                if (!empty($currentProject)) {
                    $projects[] = $currentProject;
                    $currentProject = [];
                }
                $currentProject = ['title' => $line];
            } elseif ($inProjectsSection && !empty($currentProject)) {
                // Add project description
                $currentProject['description'] = $currentProject['description'] ?? '';
                $currentProject['description'] .= ' ' . $line;
            }
        }
        
        if (!empty($currentProject)) {
            $projects[] = $currentProject;
        }
        
        return $projects;
    }
}
