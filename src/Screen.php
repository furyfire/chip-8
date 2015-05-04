<?php
namespace furyfire\chip8;

class Screen
{
    protected $pixels = array();
    protected $updated = false;
    public function __construct()
    {
        $this->clearScreen();
        $this->updated = false;
    }

    /**
     * Clear the screen
     */
    public function clearScreen()
    {
        $this->pixels = array_fill(0, 64, array_fill(0, 32, 0));
        $this->updated = true;
    }

    /**
     * Return a single pixel value
     * @param real $x
     * @param real $y
     * @return bool
     */
    public function getPixel($x, $y)
    {
        $x &= 0x3F;
        $y &= 0x1F;

        return $this->pixels[$x][$y];
    }

    public function setPixel($x, $y, $state)
    {
        $x &= 0x3F;
        $y &= 0x1F;
        $this->pixels[$x][$y] = $state;

        $this->updated = true;
    }

    /**
     * Render the current screen to ASCII text.
     */
    public function renderToText()
    {
        for ($y = 0; $y < 32; $y++) {
            for ($x = 0; $x < 64; $x++) {
                echo($this->pixels[$x][$y]) ? chr(219) : ' ';
            }
            echo "\n";
        }
        $this->updated = false;
    }

    /**
     * Render the current screen to ASCII art
     * Slightly better Height/Width ratio using half pixels.
     */
    public function renderToAsciiArt()
    {
        for ($y = 0; $y < 32; $y += 2) {
            for ($x = 0; $x < 64; $x++) {
                echo self::pixels2Ascii($this->pixels[$x][$y], $this->pixels[$x][$y+1]);
            }
            echo "\n";
        }
        $this->updated = false;
    }

    private static function pixels2Ascii($top, $bottom)
    {
        if ($top && $bottom) {
            return chr(219);
        } elseif ($top && ! $bottom) {
            return chr(223);
        } elseif (!$top && $bottom) {
            return chr(220);
        } elseif (!$top && ! $bottom) {
            return ' ';
        }
    }

    /**
     * Render the current screen to a PNG file.
     *
     * @param string $filename Path to render the screen to
     */
    public function renderToImage($filename)
    {
        $image = imagecreate(64, 32);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);

        for ($y = 0; $y < 32; $y++) {
            for ($x = 0; $x < 64; $x++) {
                $color = ($this->pixels[$x][$y]) ? $black : $white;
                imagesetpixel($image, $x, $y, $color);
            }
        }
        imagepng($image, $filename, 9, PNG_NO_FILTER);
        $this->updated = false;
    }

    /**
     * Indicates if any pixels has changed since last step of the emulator
     *
     * @return boolean true on update otherwise falls
     */
    public function updated()
    {
        return $this->updated;
    }
}
