<?php
function advptr(&$div) {
  // Advances a DOM pointer to the next Element
  do {
    $div = $div->nextSibling;
  } while($div->nodeType != XML_ELEMENT_NODE);
}

function dombr2nl($div) {
  if($div == NULL) return '';

  if($div->nodeType == XML_TEXT_NODE) {
    return $div->textContent . dombr2nl($div->nextSibling);
  } elseif($div->nodeType == XML_ELEMENT_NODE) {
    if($div->nodeName == 'br') {
      return "\n" . dombr2nl($div->nextSibling);
    } else {
      return dombr2nl($div->firstChild);
    }
  } else {
    return dombr2nl($div->nextSibling);
  }
}

function mytrim($str, $more='') {
  // Because &nbsp; is annoying
  return trim($str, "$more \t\r\n\0\x0B\xA0");
}

