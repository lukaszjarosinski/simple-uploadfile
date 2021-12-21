# Simple file upload
Klasa PHP umo¿liwiaj¹ca szybki upload pliku na serwer, z mo¿liwoœci¹ ograniczenia typów plików oraz generowania miniatur w przypadku obrazów JPG, GIF PNG. 
## Dzia³anie
Inicjalizacja klasy i szyki upload
```php
	file = new uploadFile;
	$file->destination(__DIR__."/upload/"); //docelowy katalog na serwerze
	$file->source = $_FILES['file']; //plik wskazany w formularzu
	$upload = $file->upload();
```
Zwraca nazwê pliku, który zosta³ za³adowany na serwer.
## Pozosta³e opcje
Po zadeklarowaniu klasy ustalamy kilka szczegó³ow tego, jak bêdzie dzia³a³a
maxfilesize = x - podajemy maksymalny rozmiar pliku, jaki mo¿na uploadowaæ, je¿eli nie podano, maksymalny rozmiar ustalony jest prze serwer
thumbwidth = x, thumbheight = x, thumbquality = x - parametry tworzonej miniaturki (dzia³a tylko z obrazkami), jej szerokoœæ, wysokoœæ i jakoœæ; wystarczy podaæ jeden wymiar, drugi zostanie dopasowany automatycznie
prefix = x - prefix, jakie zostanie dodany do nazwy tworzonej miniaturki
destination($destination) - miejsce docelowe, gdzie ma byc wgrany uploadowany plik. Je¿eli katalog nie istnieje, zostanie utworzony z chmod 777
thumbCreate = true lub false (domyœlnie true) - czy ma zostaæ utworzona miniaturka (dzia³a tylko z obrazkami), je¿eli nie podano, miniaturka zostanie utworzona
overwrite = true lub false (domyœlnie false) - je¿eli plik o identycznej nazwie jest ju¿ na serwerze, zostanie nadpisany. Je¿eli funkcja nie zostanie wykonana, nowemu plikowi zostanie nadana inna nazwa
source = x - Ÿród³o pliku, najczêœciej tablica $_FILES['jakas nazwa']
## Funkcje "wykonawcze"
upload() - wgrywa na serwer plik o parametrach ustalonych w konfiguracji (dzia³a z ka¿dym typem plików)
CreateThumbail() - wgrywa plik (jak poprzednio), ale dodatkowo tworzy miniaturkê o ustalonych parametrach (dzia³a tylko z obrazkami). Je¿eli jako parametr damy true, po uploadzie najpierw przycina du¿e zdjêcie do rozmiarów $this->cropped_width i $this->cropped_height, a dopiero póŸniej generuje miniaturkê
### Nowoœci w wersji 2
checkallowed = true lub false (domyœlnie true) - je¿eli ustawiono na true, podczas wgrywania na serwer zostanie sprawdzony typ wgrywanego pliku i je¿eli nie znajduje siê on w tablicy allowedfiletypes, plik nie zostanie wgrany na serwer
allowedfiletypes - tablica zawieraj¹ca typy mime plików, które mog¹ zostaæ wgrane na serwerze
delete($file) - usuniêcie pliku o podanej œcie¿ce
mime_content_type($file) - sprawdzenie typu mime podanego pliku
W wersji 2 pominiêto komunikaty b³êdów w pliku zewnêtrznym, sk³adaj¹ siê one teraz z kilku informacji w jêzyku angielskim
## Przyk³ad pe³nego u¿ycia
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
			else $return = array('type'=>'error','text'=>'Wyst¹pi³ b³¹d przy wgrywaniu pliku');
		}
	}
```