/*
 * This file is part of the YesWiki Extension nextcloudconnector.
 *
 * Authors : see README.md file that was distributed with this source code.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function getNextcloudConnectorField(readConf,writeconf,semanticConf,defaultMapping){

    return {
        field: {
            label: _t('NEXTCLOUDCONNECTOR_FILE_FIELD'),
            name: "nextcloudconnectorfichier",
            attrs: { type: "nextcloudconnectorfichier" },
            icon: '<i class="fas fa-upload"></i>',
        },
        attributes: {
            readlabel: {
              label: _t('BAZ_FORM_EDIT_FILE_READLABEL_LABEL'), 
              value: "",
              placeholder: _t('BAZ_FILEFIELD_FILE')
            },
            maxsize: { label: _t('BAZ_FORM_EDIT_FILE_MAXSIZE_LABEL'), value: "" },
            hint: { label: _t('BAZ_FORM_EDIT_HELP'), value: "" },
            read: readConf,
            write: writeconf,
            semantic: semanticConf,
        },
        attributesMapping: {
            ...defaultMapping,
            ...{
              3: "maxsize",
              6: "readlabel"
            }
        },
        renderInput(field) {
            return { 
              field: `<input type="file" name="${field.name}" class="form-control" value="" disabled/>` ,
            }
        }
    }
}