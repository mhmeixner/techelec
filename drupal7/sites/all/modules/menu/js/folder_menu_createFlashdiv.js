/**
 * Return flash div.
 */
function folder_menu_createFlash() {
  if (typeof(AC_FL_RunContent) != "undefined") {
    AC_FL_RunContent(
        "codebase", "http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0",
        "width", "1000",
        "height", "3000",
        "src", Drupal.settings.folder_menu.swf_url,
        "quality", "high",
        "pluginspage", "http://www.macromedia.com/go/getflashplayer",
        "align", "left",
        "play", "true",
        "loop", "true",
        "scale", "showall",
        "wmode", "transparent",
        "devicefont", "false",
        "id", "FolderMenuVerticalElem",
        "name", "FolderMenuVerticalElem",
        "menu", "true",
        "allowFullScreen", "false",
        "allowScriptAccess","always",
        "movie", Drupal.settings.folder_menu.swf_url,
        "salign", "t",
        "flashvars", "xmlUrl=" + Drupal.settings.folder_menu.menu_xml_url
    );

    if(typeof(FolderMenuVerticalElem) == "undefined") {
      folder_menu_initFlash_Moz_vertical();
    }
    else {
      folder_menu_initFlash_vertical();
    }

    var flash = document.getElementsByName("FolderMenuVerticalElem")[0];
    var flashContainer = document.getElementById("foldermenuverticalContainer");
    flashContainer.appendChild(flash);
    document.body.appendChild(flashContainer);
  }
}
function folder_menu_initFlash_vertical()	{
  if (typeof(FolderMenuVerticalElem.getWidth) == "function") {
    FolderMenuVerticalObj = new FolderMenuVertical("foldermenuverticalContainer", FolderMenuVerticalElem, "FolderMenuVerticalObj", folder_menu_initFlash_vertical);
  }
  else {
    setTimeout("folder_menu_initFlash_vertical()", 500);
  }
}

function folder_menu_onmouseover_vertical() {
  if (typeof(FolderMenuVerticalObj) != "undefined")	{
    FolderMenuVerticalObj.TOtrigger = true;
    FolderMenuVerticalObj.setHeightAndWidthTO();
  }
}

function folder_menu_onmouseout_vertical() {
  if (typeof(FolderMenuVerticalObj) != "undefined")	{
    FolderMenuVerticalObj.TOtrigger = false;
  }
}

function folder_menu_initFlash_Moz_vertical() {
  if (typeof(document.FolderMenuVerticalElem.getWidth) == "function") {
    FolderMenuVerticalObj = new FolderMenuVertical("foldermenuverticalContainer", document.FolderMenuVerticalElem, "FolderMenuVerticalObj", folder_menu_initFlash_Moz_vertical);
  }
  else {
    setTimeout("folder_menu_initFlash_Moz_vertical()", 500);
  }
}
