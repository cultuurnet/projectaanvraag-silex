<?php

namespace CultuurNet\ProjectAanvraag\Utility;

trait TextSummaryTrait
{

    /**
     * Create a summary for the given text.
     * @param $text
     * @param int $size
     * @return bool|string
     */
    public function createSummary($text, $size = 300)
    {

        // If the size is zero, the entire body is the summary.
        if ($size == 0) {
            return $text;
        }

        // If we have a short body, the entire body is the summary.
        if (Unicode::strlen($text) <= $size) {
            return $text;
        }

        // If the delimiter has not been specified, try to split at paragraph or
        // sentence boundaries.

        // The summary may not be longer than maximum length specified. Initial slice.
        $summary = Unicode::truncate($text, $size);

        // Store the actual length of the UTF8 string -- which might not be the same
        // as $size.
        $maxRpos = strlen($summary);

        // How much to cut off the end of the summary so that it doesn't end in the
        // middle of a paragraph, sentence, or word.
        // Initialize it to maximum in order to find the minimum.
        $minRpos = $maxRpos;

        // Store the reverse of the summary. We use strpos on the reversed needle and
        // haystack for speed and convenience.
        $reversed = strrev($summary);

        // Build an array of arrays of break points grouped by preference.
        $breakPoints = [];

        // A paragraph near the end of sliced summary is most preferable.
        $breakPoints[] = ['</p>' => 0];

        // If no complete paragraph then treat line breaks as paragraphs.
        $lineBreaks = ['<br />' => 6, '<br>' => 4];
        $breakPoints[] = $lineBreaks;

        // If the first paragraph is too long, split at the end of a sentence.
        $breakPoints[] = ['. ' => 1, '! ' => 1, '? ' => 1, '。' => 0, '؟ ' => 1];

        // Iterate over the groups of break points until a break point is found.
        foreach ($breakPoints as $points) {
            // Look for each break point, starting at the end of the summary.
            foreach ($points as $point => $offset) {
                // The summary is already reversed, but the break point isn't.
                $rpos = strpos($reversed, strrev($point));
                if ($rpos !== false) {
                    $minRpos = min($rpos + $offset, $minRpos);
                }
            }

            // If a break point was found in this group, slice and stop searching.
            if ($minRpos !== $maxRpos) {
                // Don't slice with length 0. Length must be <0 to slice from RHS.
                $summary = ($minRpos === 0) ? $summary : substr($summary, 0, 0 - $minRpos);
                break;
            }
        }

        return $summary;
    }
}
