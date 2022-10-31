<?

AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", Array("TestWork", "beforeElementUpdateDate")); // задание 1
AddEventHandler("iblock", "OnBeforeIBlockElementDelete", Array("TestWork", "beforeElementDeleteCounter")); // задание 2


class TestWork{
	
	function beforeElementDeleteCounter($ID){
		
		$catalog_ib = 21; // по идее должен быть прописан константой в dbconn.php
		$deleteTimeError = 604800; /* минимальное время запрета удаления (неделя) */
		
		global $USER;
		$arGroups = $USER->GetUserGroupArray();
		$user_id = $USER->GetID();
		$user_login = $USER->GetLogin();
		
		$res = CIBlockElement::GetByID($ID);
		if($ar_res = $res->GetNext()){
			
			/* если просмотров больше 100000 и пользователь не входит в группу 1 (администраторы) */
			if($ar_res['SHOW_COUNTER'] > 10000 && !in_array(1,$arGroups)){
				global $APPLICATION;
				$APPLICATION->throwException("\n\r Нельзя удалить данный товар, так как он очень популярный на сайте.");
				/* отправляем письмо админу */
				$arEventFields = array("MESSAGE"=> "Пользователь *{$user_login}*[{$user_id}] пытается удалить популярный товар {$ar_res['NAME']}, у которого {$ar_res['SHOW_COUNTER']} показов на сайте.");
				CEvent::Send("SEND_ADMIN_INFO", SITE_ID, $arEventFields);
				/* PS: тут пишу по памяти, без дебага, ибо время уже поджимает */
				
				return false;		
			}
			
			
			/* этот кусок я перепутал с первым заданием (вместо запрета на изменение сделал запрет на удаление) */
			/* if((time() - $ar_res['DATE_CREATE_UNIX']) < $deleteTimeError && $ar_res['IBLOCK_ID'] == $catalog_ib){
				global $APPLICATION;
				$APPLICATION->throwException("\n\r Товар {$ar_res['NAME']} был создан менее одной недели назад и не может быть удален.");
				return false;		
			} */
		}
    }
	
	function beforeElementUpdateDate(&$arFields){
		$catalog_ib = 21; // по идее должен быть прописан константой в dbconn.php
		$deleteTimeError = 604800; /* минимальное время запрета удаления (неделя) */
		$res = CIBlockElement::GetByID($arFields['ID']);
		if($ar_res = $res->GetNext()){
			if((time() - $ar_res['DATE_CREATE_UNIX']) < $deleteTimeError && $ar_res['IBLOCK_ID'] == $catalog_ib){
				global $APPLICATION;
				$APPLICATION->throwException("Товар {$ar_res['NAME']} был создан менее одной недели назад и не может быть изменен.");
				return false;		
			}
		}
    }
	
	
	
	/* Задание №3
		в коде идет запрос getList в инфоблок товаров, в переборе результатов которого пробрасываются запросы в инфоблок брендов.
	
		как минимум - код очень тяжелый. на каждую итеррацию перебора товаров мы делаем запрос в инфоблок брендов по айдишнику, и все это только для того, чтобы построить массив с элементами для слайдера.
		упростить можно как минимум так: 
		запрос в инфоблок товаров с условием "поле бренд не пустое". и затем при переборе заполнить массив брендов айдишниками:
		$brands[] = $properties['BRAND']['VALUE'];
		и только после этого, когда у нас будет собран массив айдишников брендов пробросить запрос в инфоблок брендов, чтобы получить список брендов:
		$res = CIBlockElement::GetList(Array(), array("IBLOCK_ID"=>"инфоблокБрендов", "ID"=>$brands), false, array(), array(*));
		
		и то, лучше тут использовать не запрос к списку элементов, а компонент, например news.list с указанием параметра FILTER_NAME, в который и передадим наш массив с айдишниками брендов:
		$GLOBALS['filterName'] = array('=ID' => $brands);
		

	*/
}

?>