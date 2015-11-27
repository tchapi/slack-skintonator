<?php

class Skintonator {

  private static $SLACK_CHARS_LIMIT = 4000; // In mother fucking characters
  private static $IMAGE_SIZE_LIMIT = 16; // In mother fucking pixels

  private static $WHITE_CHAR = ":white_square:";
  private static $STANDARD_CHAR = ":skin-tone-%d:";

  public function getPixel($image, $x, $y) {

    // Turn to motherfucking grayscale
    imagefilter($image, IMG_FILTER_GRAYSCALE);

    // Extract the motherfucking color
    $colors = imagecolorsforindex($image, imagecolorat($image, $x, $y));

    // Invert the shit, cast to fucking float
    $Y = 1 - floatval($colors['red'])/255.0;

    // Level down the shit
    if ($colors['alpha'] != 0 || $Y < 0.1) { 
      return 0;
    } else {
      return intval($Y * 4) + 2; // Linearize — Skin tone is from 2 to 6 only
    }

  }

  public function run($image) {

    $string = "";
    $image_res = imagecreatefrompng($image);
    list($width, $height, $type, $attr) = getimagesize($image);

    // WHAAAT ?
    if ($height > static::$IMAGE_SIZE_LIMIT || $width > static::$IMAGE_SIZE_LIMIT) {
      throw new Exception("Resulting text is too long for Slack (4000 char.)", 1);
    }

    // Iterate on the pixels, X and Y are inverted
    for($x=0; $x<$width; $x++)
    {
        for($y=0; $y<$height; $y++)
        {
          $pixel = $this->getPixel($image_res, $y, $x);

          if ($pixel != 0) {
            $string .= sprintf(static::$STANDARD_CHAR, $pixel);
          } else {
            $string .= static::$WHITE_CHAR;
          }

          $string .= " ";

        }

        $string .= "\n";
    }

    if (strlen($string) > static::$SLACK_CHARS_LIMIT) {
      throw new Exception("Resulting text is too long for Slack (4000 char.)", 1);
    }

    return $string;

  }

}

// Init the fuck
$s = new Skintonator();

// When shit hits the fan
try {
  echo $s->run($argv[1]);
} catch(Exception $e) {
  echo "Wooops : " . $e->getMessage() . "\n";
}
