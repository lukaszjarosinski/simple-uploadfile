# Simple file upload
Klasa PHP umo�liwiaj�ca szybki upload pliku na serwer, z mo�liwo�ci� ograniczenia typ�w plik�w oraz generowania miniatur w przypadku obraz�w JPG, GIF PNG. 
## Dzia�anie
Inicjalizacja klasy i szyki upload
```php
	file = new uploadFile;
	$file->destination(__DIR__."/upload/"); //docelowy katalog na serwerze
	$file->source = $_FILES['file']; //plik wskazany w formularzu
	$upload = $file->upload();
```
Zwraca nazw� pliku, kt�ry zosta� za�adowany na serwer.
## Pozosta�e opcje
Po zadeklarowaniu klasy ustalamy kilka szczeg�ow tego, jak b�dzie dzia�a�a
maxfilesize = x - podajemy maksymalny rozmiar pliku, jaki mo�na uploadowa�, je�eli nie podano, maksymalny rozmiar ustalony jest prze serwer
thumbwidth = x, thumbheight = x, thumbquality = x - parametry tworzonej miniaturki (dzia�a tylko z obrazkami), jej szeroko��, wysoko�� i jako��; wystarczy poda� jeden wymiar, drugi zostanie dopasowany automatycznie
prefix = x - prefix, jakie zostanie dodany do nazwy tworzonej miniaturki
destination($destination) - miejsce docelowe, gdzie ma byc wgrany uploadowany plik. Je�eli katalog nie istnieje, zostanie utworzony z chmod 777
thumbCreate = true lub false (domy�lnie true) - czy ma zosta� utworzona miniaturka (dzia�a tylko z obrazkami), je�eli nie podano, miniaturka zostanie utworzona
overwrite = true lub false (domy�lnie false) - je�eli plik o identycznej nazwie jest ju� na serwerze, zostanie nadpisany. Je�eli funkcja nie zostanie wykonana, nowemu plikowi zostanie nadana inna nazwa
source = x - �r�d�o pliku, najcz�ciej tablica $_FILES['jakas nazwa']
## Funkcje "wykonawcze"
upload() - wgrywa na serwer plik o parametrach ustalonych w konfiguracji (dzia�a z ka�dym typem plik�w)
CreateThumbail() - wgrywa plik (jak poprzednio), ale dodatkowo tworzy miniaturk� o ustalonych parametrach (dzia�a tylko z obrazkami). Je�eli jako parametr damy true, po uploadzie najpierw przycina du�e zdj�cie do rozmiar�w $this->cropped_width i $this->cropped_height, a dopiero p�niej generuje miniaturk�
### Nowo�ci w wersji 2
checkallowed = true lub false (domy�lnie true) - je�eli ustawiono na true, podczas wgrywania na serwer zostanie sprawdzony typ wgrywanego pliku i je�eli nie znajduje si� on w tablicy allowedfiletypes, plik nie zostanie wgrany na serwer
allowedfiletypes - tablica zawieraj�ca typy mime plik�w, kt�re mog� zosta� wgrane na serwerze
delete($file) - usuni�cie pliku o podanej �cie�ce
mime_content_type($file) - sprawdzenie typu mime podanego pliku
W wersji 2 pomini�to komunikaty b��d�w w pliku zewn�trznym, sk�adaj� si� one teraz z kilku informacji w j�zyku angielskim
## Przyk�ad pe�nego u�ycia
```php
	if (isset($_FILES['image']) AND $_FILES['image'] != '')
	{
		include_once('classes/class.uploadfile.php');
		$file = new uploadFile;
		$file->destination("gallery/");
		$file->source = $_FILES['image'];
		$file->prefix = "mini_";
		$file->thumbWidth = 250;
		$file->thumbQuality = 90;
		$file->thumbCreate = true;

		$file->crop = true;
		$file->cropped_width = 800+20;
		$file->cropped_height = 800+20;

		$file->CreateThumbail();
		if (!$file->error)
		{
			if ($file->getFileName() <> '') 
			{
				$temp = explode(".",$file->getFileName());
				$return = array('type'=>'ok','short'=>$temp[0],'text'=>$file->getFileName());
				$image = $file->getFileName();
			}
			else $return = array('type'=>'error','text'=>'Wyst�pi� b��d przy wgrywaniu pliku');
		}
	}
```