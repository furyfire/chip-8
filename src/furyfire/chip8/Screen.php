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

    public function clearScreen()
    {
        for ($x = 0; $x < 64; $x++) {
            for ($y = 0; $y < 32; $y++) {
                $this->pixels[$x][$y] = 0;
            }
        }
        $this->updated = true;
    }

    public function getPixel($x, $y)
    {
        $x &= 0x3F;
        $y &= 0x1F;

        return $this->pixels[$x][$y];
    }

    public function setPixel($x, $y, $state = true)
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
                if ($this->pixels[$x][$y]  and  $this->pixels[$x][$y+1]) {
                    echo chr(219);
                } elseif ($this->pixels[$x][$y]  and !$this->pixels[$x][$y+1]) {
                    echo chr(223);
                } elseif (!$this->pixels[$x][$y] and  $this->pixels[$x][$y+1]) {
                    echo chr(220);
                } elseif (!$this->pixels[$x][$y] and !$this->pixels[$x][$y+1]) {
                    echo ' ';
                }
            }
            echo "\n";
        }
        $this->updated = false;
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

    public function updated()
    {
        return $this->updated;
    }
}
