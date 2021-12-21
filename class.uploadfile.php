<?
/*
Copyright 2021 by Łukasz Jarosiński
www.lukaszjarosinski.pl
tel. 508 052 990
*/
//Klasa obsługi uploadu plików i manipulacji obrazkami v2 - copyright by WAWRUS 2010
//Po zadeklarowaniu klasy ustalamy kilka szczegółow tego, jak będzie działała
//maxfilesize = x - podajemy maksymalny rozmiar pliku, jaki można uploadować, jeżeli nie podano, maksymalny rozmiar ustalony jest prze serwer
//thumbwidth = x, thumbheight = x, thumbquality = x - parametry tworzonej miniaturki (działa tylko z obrazkami), jej szerokość, wysokość i jakość; wystarczy podać jeden wymiar, drugi zostanie dopasowany automatycznie
//prefix = x - prefix, jakie zostanie dodany do nazwy tworzonej miniaturki
//destination($destination) - miejsce docelowe, gdzie ma byc wgrany uploadowany plik. Jeżeli katalog nie istnieje, zostanie utworzony z chmod 777
//thumbCreate = true lub false (domyślnie true) - czy ma zostać utworzona miniaturka (działa tylko z obrazkami), jeżeli nie podano, miniaturka zostanie utworzona
//overwrite = true lub false (domyślnie false) - jeżeli plik o identycznej nazwie jest już na serwerze, zostanie nadpisany. Jeżeli funkcja nie zostanie wykonana, nowemu plikowi zostanie nadana inna nazwa
//source = x - źródło pliku, najczęściej tablica $_FILES['jakas nazwa']
//Funkcje "wykonawcze"
//upload() - wgrywa na serwer plik o parametrach ustalonych w konfiguracji (działa z każdym typem plików)
//CreateThumbail() - wgrywa plik (jak poprzednio), ale dodatkowo tworzy miniaturkę o ustalonych parametrach (działa tylko z obrazkami). Jeżeli jako parametr damy true, po uploadzie najpierw przycina duże zdjęcie do rozmiarów $this->cropped_width i $this->cropped_height, a dopiero później generuje miniaturkę
//Nowości w wersji 2
//checkallowed = true lub false (domyślnie true) - jeżeli ustawiono na true, podczas wgrywania na serwer zostanie sprawdzony typ wgrywanego pliku i jeżeli nie znajduje się on w tablicy allowedfiletypes, plik nie zostanie wgrany na serwer
//allowedfiletypes - tablica zawierająca typy mime plików, które mogą zostać wgrane na serwerze
//delete($file) - usunięcie pliku o podanej ścieżce
//mime_content_type($file) - sprawdzenie typu mime podanego pliku
//W wersji 2 pominięto komunikaty błędów w pliku zewnętrznym, składają się one teraz z kilku informacji w języku angielskim
class UploadFile
{
	var $error;
	var $thumbcreate = true;
	var $uploaded;
	var $dest;
	var $overwrite = false;
	var $name;
	var $prefix = "mini_";
	var $thumbWidth;
	var $thumbHeight;
	var $thumbQuality = 80;
	var $maxFileSize = 500000;
	var $allowedfiletypes = array('image/jpeg','image/pjpeg','image/gif','image/png','application/pdf','application/zip');
	var $checkallowed = true;
	var $source;
	var $cropped_width;
	var $cropped_height;
	var $scaleMethod;
	var $crop = false;

	function destination($destination)
	{
		if (!file_exists($destination)) mkdir($destination,0777);
		$this->dest = $destination;
	}
	
	function delete($file)
	{
		if (file_exists($file)) 
		{
		unlink($file);
		return true;
		}
		else return false;
	}

	function upload()
	{
		if (is_uploaded_file($this->source['tmp_name']))
		{
			if ($this->source['size'] > $this->maxFileSize)
			{
				$this->Error(3);
				return;
			}
			else
			{
				if ((file_exists($this->dest.$this->source['name']) OR file_exists($this->dest.stripText($this->source['name']))) AND !$this->overwrite) 
				{
					$newname = substr(md5(uniqid($this->source['name'])),0,8).$this->source['name'];
					$this->uploaded = move_uploaded_file($this->source['tmp_name'],$this->dest.stripText($newname));
				}
				elseif (file_exists($this->dest.$this->source['name']) AND $this->overwrite) 
				{
				  $newname = "";
					unlink($this->dest.$this->source['name']);
					$this->uploaded = move_uploaded_file($this->source['tmp_name'],$this->dest.stripText($this->source['name']));
				}
				else 
				{
				$this->uploaded = move_uploaded_file($this->source['tmp_name'],$this->dest.stripText($this->source['name']));
				$newname = "";
				}
			}
		}
		if (!$this->uploaded) 
		{
			$this->Error(1);
			return;
		}
		else
		{
			if ($this->source['name'] AND $newname <> '') $this->name = stripText($newname);
			else $this->name = stripText($this->source['name']);
			if ($this->checkallowed)
			{
				if(in_array($this->fileType(),$this->allowedfiletypes)) return $this->name;
				else 
				{
					$this->delete($this->dest.$this->name);
					$this->Error(4);
					return;
				}
			}
			else return $this->name;
		}
	}

	function mime_content_type($filename) {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );
				$exploded = explode('.',$filename);
        $ext = strtolower(array_pop($exploded));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else {
            return 'application/octet-stream';
        }
}

	function fileType()
	{
		return $this->mime_content_type($this->dest.$this->name);
	}

	function CreateThumbail()
	{
		$name = $this->upload();
		$type = $this->fileType();
		if ($this->crop)
		{
			$this->resize($this->dest.$this->name,$this->cropped_width,$this->cropped_height,"2-".$name);
		}
		if ($this->thumbcreate AND !$this->error)
		{
			if ($type == 'image/jpeg' OR $type == 'image/pjpeg') 
			{
				if ($this->crop) 
				{
					$this->jpegThumb("2-".$name);
				}
				else $this->jpegThumb($name);
			}
			elseif ($type == 'image/gif') 
			{
				if ($this->crop) 
				{
					$this->gifThumb("2-".$name);
				}
				else $this->gifThumb($name);
			}
			elseif ($type == 'image/png')
			{
				if ($this->crop)
				{
					$this->pngThumb("2-".$name);
				}
				else $this->pngThumb($name);
			}
			else $this->Error(2);
		}
		else $this->Error(2);
	}

	function Error($code)
	{
		switch($code)
		{
			case "1": $this->error = 'upload_error'; break;
			case "2": $this->error = 'thumb_create_error'; break;
			case "3": $this->error = 'file_too_big'; break;
			case "4": $this->error = 'not_allowed_filetype'; break;
		}
		return $this->error;
	}

	function jpegThumb($name)
	{
		$img = imagecreatefromjpeg($this->dest.$name);
		$rozmiar = getimagesize($this->dest.$name);
		$szerokosc = $rozmiar[0];
		$wysokosc = $rozmiar[1];
		if ($szerokosc > $this->thumbWidth)
		{
			$width  = ImageSx($img);
			$height = ImageSy($img);
			if ($this->thumbWidth <> '' AND $this->thumbWidth > 0 AND $this->thumbHeight <> '' AND $this->thumbHeight > 0)
			{
				$width_mini = $this->thumbWidth;
				$height_mini = $this->thumbHeight;
			}
			if ($this->thumbWidth <> '' AND $this->thumbWidth > 0 AND ($this->thumbHeight == '' OR $this->thumbHeight <= 0))
			{
				$factor = $szerokosc/$wysokosc;
				$width_mini = $this->thumbWidth;
				$height_mini = floor($width_mini/$factor);
			}
			if (($this->thumbWidth == '' OR $this->thumbWidth <= 0) AND $this->thumbHeight <> '' AND $this->thumbHeight > 0)
			{
				$factor = $wysokosc/$szerokosc;
				$height_mini = $this->thumbHeight;
				$width_mini = floor($height_mini/$factor);
			}
			$img_mini = imagecreatetruecolor($width_mini, $height_mini);
			imagecopyresampled($img_mini, $img, 0, 0, 0, 0, $width_mini , $height_mini, $width  , $height);
			imagejpeg($img_mini, $this->dest.$this->prefix.$this->name, $this->thumbQuality);
			imagedestroy($img);
			imagedestroy($img_mini);
		}
	}

	function gifThumb($name)
	{
		$img = imagecreatefromgif($this->dest.$name);
		$rozmiar = getimagesize($this->dest.$name);
		$szerokosc = $rozmiar[0];
		$wysokosc = $rozmiar[1];
		if ($szerokosc > $this->thumbWidth)
		{
			$width  = ImageSx($img);
			$height = ImageSy($img);
			if ($this->thumbWidth <> '' AND $this->thumbWidth > 0 AND $this->thumbHeight <> '' AND $this->thumbHeight > 0)
			{
				$width_mini = $this->thumbWidth;
				$height_mini = $this->thumbHeight;
			}
			if ($this->thumbWidth <> '' AND $this->thumbWidth > 0 AND ($this->thumbHeight == '' OR $this->thumbHeight <= 0))
			{
				$factor = $szerokosc/$wysokosc;
				$width_mini = $this->thumbWidth;
				$height_mini = floor($width_mini/$factor);
			}
			if (($this->thumbWidth == '' OR $this->thumbWidth <= 0) AND $this->thumbHeight <> '' AND $this->thumbHeight > 0)
			{
				$factor = $wysokosc/$szerokosc;
				$height_mini = $this->thumbHeight;
				$width_mini = floor($height_mini/$factor);
			}
			$img_mini = imagecreatetruecolor($width_mini, $height_mini);
			imagecopyresampled($img_mini, $img, 0, 0, 0, 0, $width_mini , $height_mini, $width  , $height);
			imagegif($img_mini, $this->dest.$this->prefix.$this->name, $this->thumbQuality);
			imagedestroy($img);
			imagedestroy($img_mini);
		}
	}

	function pngThumb($name)
	{
		$img = imagecreatefrompng($this->dest.$name);
		$rozmiar = getimagesize($this->dest.$name);
		$szerokosc = $rozmiar[0];
		$wysokosc = $rozmiar[1];
		if ($szerokosc > $this->thumbWidth)
		{
			$width  = ImageSx($img);
			$height = ImageSy($img);
			if ($this->thumbWidth <> '' AND $this->thumbWidth > 0 AND $this->thumbHeight <> '' AND $this->thumbHeight > 0)
			{
				$width_mini = $this->thumbWidth;
				$height_mini = $this->thumbHeight;
			}
			if ($this->thumbWidth <> '' AND $this->thumbWidth > 0 AND ($this->thumbHeight == '' OR $this->thumbHeight <= 0))
			{
				$factor = $szerokosc/$wysokosc;
				$width_mini = $this->thumbWidth;
				$height_mini = floor($width_mini/$factor);
			}
			if (($this->thumbWidth == '' OR $this->thumbWidth <= 0) AND $this->thumbHeight <> '' AND $this->thumbHeight > 0)
			{
				$factor = $wysokosc/$szerokosc;
				$height_mini = $this->thumbHeight;
				$width_mini = floor($height_mini/$factor);
			}
			$img_mini = imagecreatetruecolor($width_mini, $height_mini);
			imagecopyresampled($img_mini, $img, 0, 0, 0, 0, $width_mini , $height_mini, $width  , $height);
			imagepng($img_mini, $this->dest.$this->prefix.$this->name);
			imagedestroy($img);
			imagedestroy($img_mini);
		}
	}
	function getFileName()
	{
		return $this->name;
	}
	function resize($imgFile, $width, $height, $file_name = null, &$error = null)
	{
		$attrs = @getimagesize($imgFile);
		if($attrs == false or ($attrs['mime'] != 'image/jpeg' AND $attrs['mime'] != 'image/pjpeg' AND $attrs['mime'] != 'image/png' AND $attrs['mime'] != 'image/gif'))
		{
			$error = "Uploaded image is not JPEG, PNG, GIF or is not readable by this page.";
			return false;
		}
		if($attrs[0] * $attrs[1] > 3000000)
		{
			$error = "Max pixels allowed is 3,000,000. Your ".$attrs[0]." x ".$attrs[1]." image has " . $attrs[0] * $attrs[1] .  " pixels.";
			return false;
		}
		$ratio = (($attrs[0] / $attrs[1]) < ($width / $height)) ? $width / $attrs[0] : $height / $attrs[1];
		$x = max(0, round($attrs[0] / 2 - ($width / 2) / $ratio));
		$y = max(0, round($attrs[1] / 2 - ($height / 2) / $ratio));
		if ($attrs['mime'] == 'image/jpeg' OR $attr['mime'] == 'image/pjpeg') $src = imagecreatefromjpeg($imgFile);
		if ($attrs['mime'] == 'image/png') $src = imagecreatefrompng($imgFile);
		if ($attrs['mime'] == 'image/gif') $src = imagecreatefromgif($imgFile);
		if($src == false)
		{
			$error = "Unknown problem trying to open uploaded image.";
			return false;
		}
		$resized = imagecreatetruecolor($width, $height);
		$result = imagecopyresampled($resized, $src, 0, 0, $x, $y, $width, $height, round($width / $ratio, 0), round($height / $ratio));
		if($result == false)
		{
			$error = "Error trying to resize and crop image.";
			return false;
		}
		else
		{
			$newname = ($file_name != '')?$this->dest.$file_name:$this->dest.$imgFile;
			if ($attrs['mime'] == 'image/jpeg' OR $attr['mime'] == 'image/pjpeg') $src = imagejpeg($resized, $newname, $this->thumbQuality);
			if ($attrs['mime'] == 'image/png') $src = imagepng($resized, $newname);
			if ($attrs['mime'] == 'image/gif') $src = imagegif($resized, $newname, $this->thumbQuality);
			//imagedestroy($src);
			imagedestroy($resized);
		}
	}
}
?>