<?php
class CGoodcodeIBlockHelper{
	function codeIdSwitcher($params){
	// The function convert ID to CODE and vice versa
	// CGoodcodeIBlockHelper::codeIdSwitcher(array("IBLOCK_CODEorID", 'SECTION_CODEorID', 'ELEMENT_CODEorID'));
    	CModule::IncludeModule("iblock"); // turn on the iblock module
    	// Getting ID of the current article
    	if ($params["ELEMENT_CODE"] || $params["ELEMENT_ID"]){
        	$dbElement = CIBlockElement::GetList(
        	    false, // ordering
        	    array( // filtering
        	        "IBLOCK_ID"=>$params["IBLOCK_ID"],
        	        "IBLOCK_CODE"=>$params["IBLOCK_CODE"],
        	        "CODE"=>$params["ELEMENT_CODE"],
        	        "ID"=>$params["ELEMENT_ID"],
					"SITE_ID" => SITE_ID
                ),
        	    false,
        	    false,
        	    array("ID", "CODE") // selecting
            );
            $thisElement = $dbElement->GetNext();
            if ($params["ELEMENT_CODE"]){
                return $thisElement["ID"];
            } else {
                return $thisElement["CODE"];
            }

        } elseif ($params["SECTION_CODE"] || $params["SECTION_ID"]) {
            $dbElement = CIBlockSection::GetList(
        	    false, // ordering
        	    array( // filtering
        	        "IBLOCK_ID"=>$params["IBLOCK_ID"],
        	        "IBLOCK_CODE"=>$params["IBLOCK_CODE"],
        	        "CODE"=>$params["SECTION_CODE"],
        	        "ID"=>$params["SECTION_ID"],
					"SITE_ID" => SITE_ID
                ),
        	    false,
        	    false,
        	    array("ID", "CODE") // selecting
            );
            $thisElement = $dbElement->GetNext();
            if ($params["SECTION_CODE"]){
                return $thisElement["ID"];
            } else {
                return $thisElement["CODE"];
            }	
        } else {
            $dbElement = CIBlock::GetList(
        	    false, // ordering
        	    array( // filtering
        	        "CODE"=>$params["IBLOCK_CODE"],
        	        "ID"=>$params["IBLOCK_ID"],
					"SITE_ID" => SITE_ID
                )
            );
            $thisElement = $dbElement->GetNext();
            if ($params["IBLOCK_CODE"]){
                return $thisElement["ID"];
            } else {
                return $thisElement["CODE"];
            }
        }
    }

    function getElementProps($arProps , $ar_OBTAINED_PROPS){
    // function returns properties of the element by it's Code or ID
	// CGoodcodeIBlockHelper::getElementProps(array("IBLOCK_ID", "ELEMENT_ID"))
        $arRecProps = array();
        CModule::IncludeModule("iblock"); // turn on iblock module
        // convert CODE to ID
        if ($arProps["ELEMENT_CODE"]){
            $arProps["ELEMENT_ID"] = CGoodcodeIBlockHelper::codeIdSwitcher(array("IBLOCK_ID"=>$arProps["IBLOCK_ID"], "ELEMENT_CODE"=>$arProps["ELEMENT_CODE"]));
        }
        // harvest elements
        foreach ($ar_OBTAINED_PROPS as $property){
            $dbRecProps =CIBlockElement::GetList(array("SORT"=>"DESC"),
                                        array("IBLOCK_ID" => $arProps["IBLOCK_ID"], "ID" => $arProps["ELEMENT_ID"]),
                                        false,
                                        false,
                                        array($property)
                                );
            // Writing all props to the array
            while($arRecProp = $dbRecProps->GetNext()){
                $arRecProps[$property][] = $arRecProp;
            }
        }
        return $arRecProps;
    }

    function getSectionProps($arProps , $ar_OBTAINED_PROPS){
    // function returns properties of the section by it's Code or ID
	// CGoodcodeIBlockHelper::getSectionProps(array("IBLOCK_ID", "SECTION_ID"))
        if ($arProps["SECTION_CODE"]){
            $arProps["SECTION_ID"] = CGoodcodeIBlockHelper::codeIdSwitcher(array("IBLOCK_ID"=>$arProps["IBLOCK_ID"], "SECTION_CODE"=>$arProps["ELEMENT_CODE"]));
        }
        $dbRecProps =CIBlockSection::GetList(
                                        array("SORT"=>"DESC"),
                                        array("IBLOCK_ID" => $arProps["IBLOCK_ID"], "ID" => $arProps["SECTION_ID"]),
                                        false,
                                        $ar_OBTAINED_PROPS,
                                        false
                                    );
        $arRecProps = array();
        // Writing all linked news IDs to an array
        while($arRecProp = $dbRecProps->GetNext()){
            $arRecProps[] = $arRecProp;
        }
        return $arRecProps;
    }

    function getSurroundElementsID($arProperties){
    // Get IDs of surround elements
    // CGoodcodeIBlockHelper::getSurroundElementsID(array("IBLOCK_ID", "ELEMENT_CODE", "SORT", "SORT_ORDER"));
	// Check the sorting
	if (!$arProperties["SORT"] || !$arProperties["SORT_ORDER"]){
            $arProperties["SORT"] = "ACTIVE_FROM";
            $arProperties["SORT_ORDER"] = "DESC";
        }
        CModule::IncludeModule("iblock"); // turn on the iblock module
    	// Get the ID of the current article
    	$dbNewsArticle = CIBlockElement::GetList(false, array("CODE"=>$arProperties["ELEMENT_CODE"], "IBLOCK_ID"=>$arProperties["IBLOCK_ID"]), false, false, array("ID"));
        $thisArticle = $dbNewsArticle->GetNext();
		// Listing Previous, Current and Next articles
		$dbPagination = CIBlockElement::GetList(
            array($arProperties["SORT"] => $arProperties["SORT_ORDER"]),
            array("IBLOCK_ID" => $arProperties["IBLOCK_ID"]),
            false,
            array("nPageSize" => "1","nElementID" => $thisArticle["ID"]),
            array("ID")
        );
        $surroundIDs = array("ID" => array());
        // Writing all linked news IDs to an array
        while($paginationItem = $dbPagination->GetNext()){
            $surroundIDs["ID"][] = $paginationItem["ID"];
        }
        return $surroundIDs;
	}
	function isIblockElements($IBLOCK_ID){
		CModule::IncludeModule("iblock"); // turn on iblock module
		// Getting elements from  IBlock
		CModule::IncludeModule("iblock");
		$obj_iblock = CIBlockElement::GetList(false, array("IBLOCK_ID"=>$IBLOCK_ID, "ACTIVE" => "Y"), false, false, false);
		$isElement = boolval($obj_iblock->GetNext()); // is there an element?
		return $isElement;
	}
} ?>
