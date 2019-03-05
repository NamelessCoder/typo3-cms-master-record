/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Module: TYPO3/CMS/MasterRecord/MasterRecordSync
 */
define(['jquery',
    'bootstrap'
], function($, bootstrap) {
    'use strict';

    /**
     * @type {settings: {}}
     * @exports TYPO3/CMS/MasterRecord/MasterRecordSync
     */
    var MasterRecordSync = {
        settings: {},
    };

    // expose as global object
    TYPO3.MasterRecordSync = MasterRecordSync;

    $('#masterrecord-sync-button').click(function() {
        var values = [];
        $('.masterrecord-fieldname-checkbox:checked').each(function(item) {
            values.push($(this).attr('value'));
        });
        if (values.length > 0) {
            $.ajax(
                {
                    url: TYPO3.settings.ajaxUrls['masterrecord_sync'],
                    data: {
                        table: $('#masterrecord-table').data('tablename'),
                        uid: $('#masterrecord-table').data('uid'),
                        fields: values
                    },
                    method: 'post',
                    complete: function(response) {
                        if (parseInt(response.responseText) === 1) {
                            for (var i=0; i<values.length; i++) {
                                var fieldName = values[i];
                                $('#masterrecord-fieldnames-' + fieldName).parents('tr').first().remove();
                            }
                        }
                        //console.log($.parseJSON(response.responseText));
                    }
                }
            );
        }
    });

    return MasterRecordSync;
});
