/*
 * This file is part of the YesWiki Extension nextcloudconnector.
 *
 * Authors : see README.md file that was distributed with this source code.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {readConf,writeconf,semanticConf,defaultMapping} from '../../../bazar/presentation/javascripts/form-edit-template/fields/commons/attributes.js'

const field = getNextcloudConnectorField(readConf,writeconf,semanticConf,defaultMapping)

window.formBuilderFields[field.field.name] = field
