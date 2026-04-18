<?php

namespace Modules\Doctor\Actions;

use Modules\Doctor\Models\FinalDiagnosis;
use Modules\Doctor\Models\Patient;

class VCard
{
    public function download(Patient $patient): string
    {
        $fullName = $patient->name;
        $email = $patient->email;
        $phone = $patient->phone;
        $website = route('doctor.patients.show', ['id' => $patient->id]);
        $names = FinalDiagnosis::getUniqueDiagnosisByPatientId($patient->id)->implode(', ');
        $note = $names;

        // Split name to N:last;first;middle;prefix;suffix
        [$first, $last] = $this->splitName($fullName);

        $lines = [
            'BEGIN:VCARD',
            'VERSION:3.0',
            'FN:'.$this->escape($fullName),
            'N:'.$this->escape($last).';'.$this->escape($first).';;;',
            'EMAIL;TYPE=INTERNET:'.$this->escape($email),
            'TEL;TYPE=CELL,VOICE:'.$this->escape($phone),
        ];

        if ($website) {
            $lines[] = 'URL:'.$this->escape($website);
        }

        if ($note !== '') {
            $lines[] = 'NOTE:'.$this->escapeNote($note);
        }

        $lines[] = 'END:VCARD';

        return implode("\r\n", $lines)."\r\n";
    }

    private function splitName(string $full): array
    {
        $parts = preg_split('/\s+/', $full);
        if (count($parts) >= 2) {
            $last = array_pop($parts);
            $first = implode(' ', $parts);
        } else {
            $first = $full;
            $last = '';
        }

        return [$first, $last];
    }

    private function escape(string $text): string
    {
        // Escape per vCard: comma, semicolon, backslash, and newlines
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace(',', "\,", $text);
        $text = str_replace(';', "\;", $text);

        return str_replace(["\r\n", "\r", "\n"], '\\n', $text);
    }

    private function escapeNote(string $text): string
    {
        // Same as escape (kept separate in case you want special handling later)
        return $this->escape($text);
    }

    public function getFileName($fullName): string
    {
        return $this->safeFilename($fullName).'.vcf';
    }

    private function safeFilename(string $name): string
    {
        $base = preg_replace('/[^A-Za-z0-9\-_\.]+/u', '_', $name);

        return trim($base ?: 'contact', '_');
    }
}
