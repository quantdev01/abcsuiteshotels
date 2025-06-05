/**
 *  Please read the terms of the CLUF license attached to this module(cf "licences" folder)
 *
 * @author    Línea Gráfica E.C.E. S.L.
 * @copyright Lineagrafica.es - Línea Gráfica E.C.E. S.L. all rights reserved.
 * @license   https://www.lineagrafica.es/licenses/license_en.pdf
 *            https://www.lineagrafica.es/licenses/license_es.pdf
 *            https://www.lineagrafica.es/licenses/license_fr.pdf
 */
/* global $, tables, url_module, url_cron_sync, token_pluglin, pluglin_is_syncing  */

function toggleFilter(id) {
  if ($(id).prop('checked')) {
    $(`#${$(id).attr('id')}_div`).show(400);
  } else {
    $(`#${$(id).attr('id')}_div`).hide(200);
  }
}

function removeOption(item) {
  const id = $(item).attr('id').replace('_remove', '');
  $(`#${id}_2 option:selected`).remove().appendTo(`#${id}_1`);
}

function addOption(item) {
  console.debug($(item));
  const id = $(item).attr('id').replace('_add', '');
  $(`#${id}_1 option:selected`).remove().appendTo(`#${id}_2`);
}

function executeTables(index) {
  if (index < tables.length) {
    $.ajax({
      url: url_module,
      type: 'GET',
      dataType: 'json',
      data: { ajax: true, action: 'getContentStatsNumber', type_content: tables[index] },
      success(data) {
        const { count } = data;
        $(`#content_${tables[index]}`).addClass('loading');
        $(`#object_${tables[index]}`).html(count);
        $(`#last_${tables[index]}`).html(0);
        $(`#words_${tables[index]}`).html(0);
        executeCount(index, 0);
      },
      error(XMLHttpRequest, textStatus, errorThrown) {
        console.log(`Error: ${errorThrown}`);
      },
    });
  } else {
    $('button[name=sendContents]').removeClass('disabled');
    $('button[name=sendContents]').removeAttr('disabled');
  }
}

function executeCount(index) {
  if (index < tables.length) {
    $.ajax({
      url: url_module,
      type: 'GET',
      dataType: 'json',
      data: { ajax: true, action: 'getContentWordsNumber', type_content: tables[index] },
      success(data) {
        const { count, next, last_item } = data;
        const prev = $(`#words_${tables[index]}`).html();
        let total = 0;
        let total_item = 0;

        if (last_item == '') total_item = 0;
        else total_item = parseInt(last_item);

        if (prev == '') total = parseInt(count);
        else total = parseInt(prev) + parseInt(count);

        // console.log(data);
        let percent = (data.last_item / data.items) * 100;
        percent = `${Math.round(percent).toString()}%`;
        // console.log(percent);

        $(`#words_${tables[index]}`).html(total);
        $(`#last_${tables[index]}`).html(total_item);
        $(`#bar_completed_${tables[index]}`).css('width', percent);

        if (next) {
          executeCount(index);
        } else {
          $(`#content_${tables[index]}`).removeClass('loading').addClass('completed');
          executeTables(index + 1);
        }
        // console.log(count);
      },
      error(XMLHttpRequest, textStatus, errorThrown) {
        console.log(`Error: ${errorThrown}`);
      },
    });
  }
}

function checkSyncProgress() {
  $.ajax({
    url: url_cron_sync,
    type: 'POST',
    dataType: 'json',
    timeout: 0,
    data: { ajax: true, method: 'progress', token: token_pluglin },
    success: (response) => {
      if (response.syncing) {
        setTimeout(checkSyncProgress, 3000);
        return;
      }

      if (response.has_error) {
        alert(response.message);
        document.getElementById('sync_now').classList.remove('syncing');
        return;
      }

      window.location.reload();
    },
  });
}

$(document).ready(() => {
  if (pluglin_is_syncing) {
    setTimeout(checkSyncProgress, 3000);
    document.getElementById('sync_now').classList.add('syncing');
  }

  // ÑAPA
  // first element of the word count table
  const wordCount = document.querySelector('.table-responsive > table > tbody > tr:nth-child(1) > td:nth-child(2) > span');

  if (wordCount) console.debug('word count', wordCount.textContent);

  if (wordCount && wordCount.textContent === '0') {
    document.getElementById('sync_now').click();
  }

  toggleFilter($('#language_restriction'));
  $('#language_restriction').click((e) => {
    toggleFilter(e.currentTarget);
  });
  $('#language_select_remove').click((e) => {
    removeOption(e.currentTarget);
  });
  $('#language_select_add').click((e) => {
    addOption(e.currentTarget);
  });

  // Enable submit token button only if there is any content
  const $blarloToken = $("input[name='token_blarlo']");
  const $sendTokenButton = $("button[name='sendToken']");
  $blarloToken.on('keyup', () => {
    if ($blarloToken.val().length > 0) {
      $sendTokenButton.prop('disabled', false);
    }
    if ($blarloToken.val().length < 1) {
      $sendTokenButton.prop('disabled', true);
    }
  });

  // Main form submit
  $('#languages_form').submit(() => {
    $('#language_select_2 option').each(() => {
      $(this).prop('selected', true);
    });
  });

  $('#configure_languages').submit(() => {
    $('#language_select_2 option').each(() => {
      $(this).prop('selected', true);
    });
  });
});

$(document).on('click', '#sync_now', (e) => {
  e.preventDefault();
  document.getElementById('sync_now').classList.add('syncing');

  $.ajax({
    url: url_cron_sync,
    type: 'POST',
    dataType: 'json',
    timeout: 0,
    data: { ajax: true, method: 'sync', token: token_pluglin },
  });

  setTimeout(checkSyncProgress, 5000);
});
