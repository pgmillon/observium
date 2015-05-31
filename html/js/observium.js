function url_from_form(form_id) {
  var url = document.getElementById(form_id).action;
  var partFields = document.getElementById(form_id).elements;

  for (var el, i = 0, n = partFields.length; i < n; i++) {
    el = partFields[i];
    if (el.value != '' && el.name != '') {
      if (el.multiple) {
        var multi = [];
        for (var part, ii = 0, nn = el.length; ii < nn; ii++) {
          part = el[ii];
          if (part.selected) {
            multi.push(encodeURIComponent(part.value.replace('/', '%7F'))); // 7F - (not defined in HTML 4 standard)
            //console.log(part.value);
          }
        }
        if (multi.length) {
          url += encodeURIComponent(el.name.replace('[]', '')) + '=' +
                 multi.join(',') + '/';
        }
      } else if (el.checked || el.type !== "checkbox") {
        url += encodeURIComponent(el.name) + '=' +
               encodeURIComponent(el.value.replace('/', '%7F')) + '/'; // 7F - (not defined in HTML 4 standard)
      }
    }
  }

  return url;

}

function form_to_path(form_id) {
  url = url_from_form(form_id);
  window.location.href = url;
}

function submitURL(form_id) {
  url = url_from_form(form_id);
  $(document.getElementById(form_id)).attr('action', url);
}

// jQuery specific observium additions
jQuery(document).ready(function() {
  // Enable bootstrap-switch by default for data-toogle="switch" attribute
  // See options here: http://www.bootstrap-switch.org/documentation-3.html
  $('[data-toggle="switch"]').bootstrapSwitch();
  
  // Preconfigured switch-mini
  $('[data-toggle="switch-mini"]').bootstrapSwitch('size',   'mini');
  $('[data-toggle="switch-mini"]').bootstrapSwitch('onText',  'Yes');
  $('[data-toggle="switch-mini"]').bootstrapSwitch('offText',  'No');
  // Preconfigured switch-revert
  $('[data-toggle="switch-revert"]').bootstrapSwitch('size',        'mini');
  $('[data-toggle="switch-revert"]').bootstrapSwitch('onText',        'No');
  $('[data-toggle="switch-revert"]').bootstrapSwitch('onColor',   'danger');
  $('[data-toggle="switch-revert"]').bootstrapSwitch('offText',      'Yes');
  $('[data-toggle="switch-revert"]').bootstrapSwitch('offColor', 'primary');
  
  $(".tooltip-from-element").each(function() {
    var selector = '#' + $(this).data('tooltip-id');
    $(this).qtip({
      content: $(selector),
      style: {
              classes: 'qtip-bootstrap',
      },
      position: {
              target: 'mouse',
              viewport: $(window),
              adjust: {
                      x: 7,
                      y: 2
              }
      }
    });
  });

  $("[data-rel='tooltip']").qtip({
    content: {
            attr: 'data-tooltip'
    },
    style: {
            classes: 'qtip-bootstrap',
    },
    position: {
            //target: 'mouse',
            viewport: $(window),
            adjust: {
                    x: 7,
                    y: 2
            }
    }
  })

  $('.entity-popup').each(function() {
      var entity_id   = $(this).data('eid');
      var entity_type = $(this).data('etype');

      $(this).qtip({

      content:{
          text: '<img class="" src="images/loading.gif" alt="Loading..." />',
          ajax:{
              url: 'ajax_entitypopup.php',
              type: 'POST',
              loading: false,
              data: { entity_type: $(this).data('etype'), entity_id: $(this).data('eid') },
          }
      },
      style: {
              classes: 'qtip-bootstrap',
      },
      position: {
              target: 'mouse',
              viewport: $(window),
              adjust: {
                      x: 7,
                      y: 2
              }
      }
  });
  });

  $('.tooltip-from-data').qtip({
    content: {
            attr: 'data-tooltip'
    },
    style: {
            classes: 'qtip-bootstrap',
    },
    position: {
            target: 'mouse',
            viewport: $(window),
            adjust: {
                    x: 7,
                    y: 2
            }
    }
  })
});
