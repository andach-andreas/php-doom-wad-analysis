<?php

namespace Andach\DoomWadAnalysis;

class DSDA
{
    public array $records;
    public string $wadName;

    public function __construct(string $wad)
    {
        $this->wadName = $wad;

        $url = 'https://dsdarchive.com/wads/' . urlencode($wad);
        $response = file_get_contents($url);

        $this->records = $this->parseDsdaTable($response);
    }

    private function parseDsdaTable(string $html): array
    {
        $records = [];
        $levelName = '';
        $categoryName = '';
        $count = 0;
        $recordID = 0;

        preg_match_all('/<tr.*?>(.*?)<\/tr>/is', $html, $trMatches);

        foreach ($trMatches[1] as $trArray)
        {
            $tds = $this->extractTds($trArray);
            $countTds = count($tds);

            // Account for the header.
            if (!$countTds)
            {
                continue;
            }

            // This is a comment for the previous row.
            if ($countTds === 1)
            {
                $records[$recordID]['comment'] = $this->cleanTdContent($tds[0]);
                continue;
            }

            // This is a new Level
            if ($countTds === 7)
            {
                $levelName = $this->cleanTdContent($tds[0]);
                $categoryName = $this->cleanTdContent($tds[1]);
            }

            if ($countTds === 6)
            {
                $categoryName = $this->cleanTdContent($tds[0]);
            }

            $recordID = $this->extractDemoId($tds[($countTds - 2)]);

            $records[$recordID] = [
                'level' => str_replace(' ', '', strtoupper($levelName)),
                'category' => $categoryName,
                'player' => $this->cleanTdContent($tds[($countTds - 5)]),
                'engine' => $this->cleanTdContent($tds[($countTds - 4)]),
                'note' => $this->extractNote($tds[($countTds - 3)]),
                'time' => $this->cleanTdContent($tds[($countTds - 2)]),
                'lmp_url_zip' => $this->extractWadURL($tds[($countTds - 2)]),
                'youtube_id' => $this->extractYoutubeId($tds[($countTds - 1)]),
                'youtube_link' => $this->extractYoutubeLink($tds[($countTds - 1)]),
            ];

            $count++;
        }

        return $records;
    }

    private function cleanTdContent(string $tdHtml): string
    {
        return preg_replace('/[\x{2000}-\x{200F}\x{2028}-\x{202F}\x{205F}-\x{206F}]/u', '', trim(strip_tags($tdHtml)));
    }

    private function extractDemoId(string $tdHtml): string
    {
        if (preg_match('/href="[^"]*\/(\d+)\//', $tdHtml, $match)) {
            return $match[1];
        }

        return '';
    }

    private function extractNote(string $tdHtml): string
    {
        if (preg_match('/aria-label="([^"]+)"/i', $tdHtml, $match)) {
            return trim($match[1]);
        }

        // Fallback: strip tags and extract plain text
        $text = strip_tags($tdHtml);
        $text = trim($text);

        return $text !== '' ? $text : '';
    }

    private function extractTds(string $trHtml): array
    {
        preg_match_all('/<td.*?>(.*?)<\/td>/is', $trHtml, $tdMatches);
        return $tdMatches[0];
    }

    private function extractWadURL(string $tdHtml): string
    {
        if (preg_match('/href="(\/files\/[^"]+)"/i', $tdHtml, $match)) {
            return 'https://dsdarchive.com' . $match[1];
        }
        return '';
    }

    private function extractYoutubeId(string $tdHtml): string
    {
        if (preg_match('/href="https:\/\/www\.youtube\.com\/watch\?v=([^"&]+)"/i', $tdHtml, $match)) {
            return $match[1];
        }

        return '';
    }

    private function extractYoutubeLink(string $tdHtml): string
    {
        if (preg_match('/href="(https:\/\/www\.youtube\.com\/watch\?v=[^"]+)"/i', $tdHtml, $match)) {
            return $match[1];
        }

        return '';
    }
}