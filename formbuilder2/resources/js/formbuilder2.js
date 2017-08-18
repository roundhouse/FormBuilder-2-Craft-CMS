$(document).ready(function() {
  var entryCount, formCount;
  if (window.FormBuilder2) {
    entryCount = window.FormBuilder2.entryCount;
    formCount = window.FormBuilder2.formCount;
    $('<style>#nav-formbuilder2 .subnav li:nth-child(2)::after{display:block;content:"' + formCount + '"}</style>').appendTo('head');
    return $('<style>#nav-formbuilder2 .subnav li:nth-child(3)::after{display:block;content:"' + entryCount + '"}</style>').appendTo('head');
  }
});
