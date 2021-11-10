/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 */

import {App} from 'vue';
import CardTable from '@ohrm/oxd/core/components/CardTable/CardTable.vue';
import Button from '@ohrm/oxd/core/components/Button/Button.vue';
import IconButton from '@ohrm/oxd/core/components/Button/Icon.vue';
import Pagination from '@ohrm/oxd/core/components/Pagination/Pagination.vue';
import Divider from '@ohrm/oxd/core/components/Divider/Divider.vue';
import Text from '@ohrm/oxd/core/components/Text/Text.vue';
import Form from '@ohrm/oxd/core/components/Form/Form.vue';
import FormRow from '@ohrm/oxd/core/components/Form/FormRow.vue';
import FormActions from '@ohrm/oxd/core/components/Form/FormActions.vue';
import InputField from '@ohrm/oxd/core/components/InputField/InputField.vue';
import InputGroup from '@ohrm/oxd/core/components/InputField/InputGroup.vue';
import TableFilter from '@ohrm/oxd/core/components/TableFilter/TableFilter.vue';
import Grid from '@ohrm/oxd/core/components/Grid/Grid.vue';
import GridItem from '@ohrm/oxd/core/components/Grid/GridItem.vue';

import SubmitButton from '@orangehrm/components/buttons/SubmitButton.vue';
import TableHeader from '@orangehrm/components/table/TableHeader.vue';
import RequiredText from '@orangehrm/components/labels/RequiredText.vue';
import Layout from '@orangehrm/components/layout/Layout.vue';
import DateInput from '@orangehrm/components/inputs/DateInput.vue';
import TimeInput from '@orangehrm/components/inputs/TimeInput.vue';

export default {
  install: (app: App) => {
    app.component('oxd-layout', Layout);
    app.component('oxd-card-table', CardTable);
    app.component('oxd-button', Button);
    app.component('oxd-pagination', Pagination);
    app.component('oxd-divider', Divider);
    app.component('oxd-text', Text);
    app.component('oxd-icon-button', IconButton);
    app.component('oxd-form', Form);
    app.component('oxd-form-row', FormRow);
    app.component('oxd-form-actions', FormActions);
    app.component('oxd-input-field', InputField);
    app.component('oxd-input-group', InputGroup);
    app.component('oxd-grid', Grid);
    app.component('oxd-grid-item', GridItem);
    app.component('oxd-table-filter', TableFilter);
    app.component('submit-button', SubmitButton);
    app.component('table-header', TableHeader);
    app.component('required-text', RequiredText);
    app.component('date-input', DateInput);
    app.component('time-input', TimeInput);
  },
};
