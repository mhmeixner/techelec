function FolderMenuVertical(ContDiv, MenuObj, thisObj) {
  this.setHeightAndWidth = function(v) {
    if (this.MenuObj.getWidth() > 990 || this.MenuObj.getHeight() > 2990
        || typeof (this.MenuObj.getHeight()) == 'undefined') {
      setTimeout(thisObj + ".setHeightAndWidth(0)", 500);
      return;
    }

    switch (v) {
    case 0:
      this.ContDiv.style.width = this.MenuObj.getWidth() + "px"
      this.ContDiv.style.height = (100 + this.MenuObj.getHeight()) + "px"
      break;

    case 1:
      this.ContDiv.style.width = this.MenuObj.getWidth() + "px"
      break;

    case 2:
      this.ContDiv.style.width = (50 + this.MenuObj.getWidth()) + "px"
      break;
    }
  }

  this.setHeightAndWidthTO = function() {

    if (this.TOtrigger) {
      this.setHeightAndWidth(2);
      setTimeout(thisObj + ".setHeightAndWidthTO()", 50);
    } else {
      setTimeout(thisObj + ".setHeightAndWidth(1)", 500);
    }
  }

  this.numOfTries = 0;
  this.TOtrigger = false;
  this.ContDiv = document.getElementById(ContDiv);
  this.MenuObj = MenuObj;
  this.setHeightAndWidth(0);
}
