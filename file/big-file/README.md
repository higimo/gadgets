## Найти самые крупные файлы на диске

Показывает самые объёмные папки и файлы на диске для аналитики чистки места и (или) архивации.

Пример вывода:
```
C:\Program Files (x86) .... 446 Gb
	C:\Program Files (x86)\Steam .... 439.9 Gb
		C:\Program Files (x86)\Steam\steamapps .... 431.4 Gb
			C:\Program Files (x86)\Steam\steamapps\common .... 431.4 Gb
				C:\Program Files (x86)\Steam\steamapps\common\Baldurs Gate 3 .... 144.6 Gb
					C:\Program Files (x86)\Steam\steamapps\common\Baldurs Gate 3\Data .... 143.9 Gb
				C:\Program Files (x86)\Steam\steamapps\common\Path of Exile 2 .... 114 Gb
					C:\Program Files (x86)\Steam\steamapps\common\Path of Exile 2\Bundles2 .... 99.3 Gb
						C:\Program Files (x86)\Steam\steamapps\common\Path of Exile 2\Bundles2\Streaming .... 86.2 Gb
C:\Users .... 257.7 Gb
	C:\Users\higimo .... 257.7 Gb
		C:\Users\higimo\Desktop .... 229.1 Gb
			C:\Users\higimo\Desktop\files .... 143.8 Gb
				C:\Users\higimo\Desktop\files\photos .... 76.9 Gb
			C:\Users\higimo\Desktop\sort .... 77.8 Gb
		C:\Users\higimo\AppData .... 73.1 Gb
```

Технически, самая крупная папка будет выводиться выше, то есть вложенность, как в примере будет нарушена.

## Польза

Когда диск почему-то заполнился, хочется посмотреть чем именно и как его можно разгрузить.

## Требования

window + powershell

## Использование

Просто запустить

```sh
start.bat
```

Результат будет в `scan.txt`
