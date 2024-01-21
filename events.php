<?php
defined('_JEXEC') or die;
/**
 * Скрипт сбора изображений из папки /images/koncerts и вывода на странице фотогалереи 
 * 
 * @package    CoderSite
 * @author CoderSite <info@codersite.ru>
 * 
 */

/**
 * Получает пути всех файлов и подпапок в указанной папке.
 *
 * @param  string $dir             Путь до папки (на конце со слэшем или без).
 * @param  bool   $recursive       Включить вложенные папки или нет?
 * @param  bool   $include_folders Включить ли в список пути на папки?
 *                                                        
 * @return array Вернет массив путей до файлов/папок.
 */
$dir = $_SERVER['DOCUMENT_ROOT']."/images/koncerts"; //Откуда берем исходные изображения
function get_dir_files( $dir, $recursive = true, $include_folders = false ){
	if( ! is_dir($dir) ) {
		return array();
	}

	$files = array();

	foreach( glob( "$dir/{,.}[!.,!..]*", GLOB_BRACE ) as $file ){

		if( is_dir( $file ) ){
			if( $include_folders )
				$files[] = $file;
			if( $recursive ){
				$files = array_merge( $files, call_user_func( __FUNCTION__, $file, $recursive, $include_folders ) );
			}
		}
		else
			$files[] = $file;
	}

	return $files;
}
$files = get_dir_files($dir);


$files = array_filter($files, function($v, $k){return strpos($v, "350");}, ARRAY_FILTER_USE_BOTH);

use Joomla\CMS\Image\Image;


$pathTmb = $_SERVER['DOCUMENT_ROOT'] . "/images/archive-events/tmb"; // папка для сжатых изображений
$pathFull = $_SERVER['DOCUMENT_ROOT'] . "/images/archive-events/full"; // папка для полных изображений
$filesTmb = get_dir_files($pathTmb); // Все файлы в папке
$namesTmb = [];// Имена файлов в папке
foreach ($filesTmb as $key => $value) {
	$namesTmb[] = basename($value);
}
//Функция создания превью изображений
function resizeImage($file, $width, $height, $pathTmb) {
    $name = basename($file);
    $image = new Image($file);
    $imageType = \IMAGETYPE_WEBP;
    $newFile = $pathTmb.'/'.$name;
    $image->resize($width, $height, false, Image::SCALE_INSIDE);
    $image->toFile($newFile, $imageType);
}
foreach ($files as $event) {
	if (!in_array(basename($event), $namesTmb)) { // Если файл с таким именем уже есть в папке, то не создаем новое изображение
	    if (is_file($event)) {
	      resizeImage($event, 306, 107, $pathTmb);//Создаем превью Афиши
	      resizeImage($event, 1000, '', $pathFull);//Создаем большую Афишу
	    }
	}
}
rsort($namesTmb);
?>
<link rel="stylesheet" href="/js/bootstrap.min.css" rel="stylesheet">
<link href="/js/fancybox/jquery.fancybox.min.css" rel="stylesheet">
<script src="/js/fancybox/jquery.fancybox.min.js"></script>
<div id="infinite-scroll" class="row my-5">
	<div class='col-md-4 p-2'><a data-fancybox="gallery" href='/images/archive-events/full/<?=$namesTmb[0]?>'><img src='/images/archive-events/tmb/<?=$namesTmb[0]?>'></a></div>
	<div class='col-md-4 p-2'><a data-fancybox="gallery" href='/images/archive-events/full/<?=$namesTmb[1]?>'><img src='/images/archive-events/tmb/<?=$namesTmb[1]?>'></a></div>
	<div class='col-md-4 p-2'><a data-fancybox="gallery" href='/images/archive-events/full/<?=$namesTmb[2]?>'><img src='/images/archive-events/tmb/<?=$namesTmb[2]?>'></a></div>
</div>

<script>
let block = document.getElementById('infinite-scroll');
let i = 3;//начинаем с четвертой картинки, так как первые три показываем сразу
let tmbNameFile   = <?=json_encode($namesTmb)?>;
window.addEventListener("scroll", function(){
	if (i < <?=count($filesTmb)?>) {
	  let contentHeight = block.offsetHeight;      // 1) высота блока контента вместе с границами
	  let yOffset       = window.pageYOffset;      // 2) текущее положение скролбара
	  let window_height = window.innerHeight;      // 3) высота внутренней области окна документа
	  let y             = yOffset + window_height;
	  // если пользователь достиг конца
	  if(y >= contentHeight) {
		 //загружаем новое содержимое в элемент
		 block.innerHTML = block.innerHTML + "<div class='col-md-4 p-2'><a data-fancybox='gallery' href='/images/archive-events/full/"+tmbNameFile[i]+"'><img src='/images/archive-events/tmb/"+tmbNameFile[i]+"'></a></div>";
		 i++;
	  }
	}
});
</script>