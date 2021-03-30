<?php
namespace Armagedon007\Converters;

use Imagick;

/**
 * Класс для конвертирования pdf, ppt, pptx в картинки
 *
 * @package     converters
 * @copyright   2021 https://iwebprofi.ru/
 * @author      Скасырский Юрий <000yurik000@gmail.com>
 * @version     1.0
 * @since       29.03.2021
 */

 class Converter {

	protected $pdfFile;

    protected $resolution = 144;

    protected $outputFormat = 'jpg';

    protected $page = 1;

    public $imagick;

    protected $numberOfPages;

    protected $validOutputFormats = ['jpg', 'jpeg', 'png'];

    protected $layerMethod = Imagick::LAYERMETHOD_FLATTEN;

    protected $colorspace;

    protected $compressionQuality;

    protected $inputFormat;

    protected $oPHPPresentation;

    protected $_fileList = [];

     /**
	 * Конструктор
	 */
	public function __construct(string $sourceFile)
    {
        if (! file_exists($sourceFile)) {
            throw new \Exception("File `{$pdfFile}` does not exist");
        }

        $mime = mime_content_type($sourceFile); 
        if($mime == 'application/pdf') {
            //
        } else if(in_array($mime, ['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'])) {
            $pptFile = $sourceFile;
            $sourceFile = preg_replace('#(pptx?)$#iU', 'pdf', $pptFile);
            $outdir = dirname($sourceFile) . '/';
            $result = '';
            exec("HOME=/tmp libreoffice --headless -convert-to pdf --outdir {$outdir} \"{$pptFile}\"", $result);
            if (! file_exists($sourceFile)) {
                throw new \Exception("Error converting file");
            }
        } else {
            throw new \Exception("File not valid input type.");
        }

        $this->imagick = new Imagick();

        $this->imagick->pingImage($sourceFile);

        $this->numberOfPages = $this->imagick->getNumberImages();

        $this->pdfFile = $sourceFile;
    
    }

	/**
	 * Клонирование
	 * @access private Защищаем от создания через clone
	 */
	private function __clone() {}

	/**
	 * Unserialize
	 * @access private Защищаем от создания через unserialize
	 */
	private function __wakeup() {}

	/**
	 * Деструктор
	 */
	public function __destruct() {}

    public function fileList() {
        return $this->_fileList;
    }

    /**
	 * Кол-во страниц в pdf-файле
	 */
	public function getNumberOfPages(): int
    {
        return $this->numberOfPages;
    }

    /**
	 * Выбрать страницу 
	 */
	public function setPage(int $page)
    {
        if ($page > $this->getNumberOfPages() || $page < 1) {
            throw new \Exception("Page {$page} does not exist");
        }

        $this->page = $page;

        return $this;
    }

    /**
	 * Сохрнаить страницу, как изображение
	 */
	public function saveImage(string $pathToImage): bool
    {

        if (is_dir($pathToImage)) {
            $pathToImage = rtrim($pathToImage, '\/').DIRECTORY_SEPARATOR.$this->page.'.'.$this->outputFormat;
        }

        $imageData = $this->getImageData($pathToImage);

        $this->_fileList[] = $pathToImage;

        return file_put_contents($pathToImage, $imageData) !== false;
    }

    /**
	 * Сохранить каждую страницу, как изображения
	 */
	public function saveAllPagesAsImages(string $directory, string $prefix = ''): array
    {
        $numberOfPages = $this->getNumberOfPages();

        if ($numberOfPages === 0) {
            return [];
        }

        $this->_fileList = [];
        return array_map(function ($pageNumber) use ($directory, $prefix) {
            $this->setPage($pageNumber);

            $destination = "{$directory}/{$prefix}{$pageNumber}.{$this->outputFormat}";

            $this->saveImage($destination);

            return $destination;
        }, range(1, $numberOfPages));
        
    }

    /**
	 * Получить данные картинки
	 */
	public function getImageData(string $pathToImage): Imagick
    {
        /*
         * Reinitialize imagick because the target resolution must be set
         * before reading the actual image.
         */
        $this->imagick = new Imagick();

		$this->imagick->setResolution($this->resolution, $this->resolution);

        if ($this->colorspace !== null) {
            $this->imagick->setColorspace($this->colorspace);
        }

        if ($this->compressionQuality !== null) {
            $this->imagick->setCompressionQuality($this->compressionQuality);
        }

        if (filter_var($this->pdfFile, FILTER_VALIDATE_URL)) {
            return $this->getRemoteImageData($pathToImage);
        }

        $this->imagick->readImage(sprintf('%s[%s]', $this->pdfFile, $this->page - 1));

        if (is_int($this->layerMethod)) {
            $this->imagick = $this->imagick->mergeImageLayers($this->layerMethod);
        }

        $this->imagick->setFormat($this->determineOutputFormat($pathToImage));

        return $this->imagick;
    }

    /**
	 * Установить выходной формат изображения из доступных
	 */
	protected function determineOutputFormat(string $pathToImage): string
    {
        $outputFormat = pathinfo($pathToImage, PATHINFO_EXTENSION);

        if ($this->outputFormat != '') {
            $outputFormat = $this->outputFormat;
        }

        $outputFormat = strtolower($outputFormat);

        if (! $this->isValidOutputFormat($outputFormat)) {
            $outputFormat = 'jpg';
        }

        return $outputFormat;
    }

    /**
	 * Проверить выходной формат
	 */
	public function isValidOutputFormat(string $outputFormat): bool
    {
        return in_array($outputFormat, $this->validOutputFormats);
    }

    /**
	 * Задать качество картинки
	 */
	public function setCompressionQuality(int $compressionQuality)
    {
        $this->compressionQuality = $compressionQuality;

        return $this;
    }

 }