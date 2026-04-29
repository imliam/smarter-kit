<?php

declare(strict_types=1);

use Filament\Support\Colors\Color;

return [
    'color' => [
        /**
         * The primary and secondary colors are used for all accent colors throughout the UI.
         * 
         * Choose a color from the palette, or generate your own using Color::generatePalette('#000000')
         */
        'primary' => Color::Orange,
        'secondary' => Color::Amber,

        /**
         * The neutral color is used for all grays throughout the UI.
         * 
         * Choose from one of Slate, Gray, Zinc, Neutral, or Stone.
         */
        'neutral' => Color::Neutral,
    ],
];
