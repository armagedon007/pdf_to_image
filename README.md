# Convert PDF, PPT & PPTX to image files

## Dependencies

- [ImageMagick](https://github.com/ImageMagick/ImageMagick) >= 7
- GhostScript >= 9

## Usage

```
use Armagedon007\Converters\Converter;

$pathToFile = '/path_to_file/demo.ppt';
$fileSource = new Converter($pathToFile);
$fileSource->saveAllPagesAsImages($outdir, 'name_');

```
