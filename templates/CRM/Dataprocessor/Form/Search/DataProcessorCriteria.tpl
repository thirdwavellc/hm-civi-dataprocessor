{crmScope extensionKey='dataprocessor'}
{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}
{* Search criteria form elements - Find Experts *}

{* Set title for search criteria accordion *}
{capture assign=editTitle}{ts}Edit Search Criteria for Data Processor(s){/ts}{/capture}

{strip}
    <div class="crm-block crm-form-block crm-basic-criteria-form-block">
        <div class="crm-accordion-wrapper crm-case_search-accordion {if $rows}collapsed{/if}">
            <div class="crm-accordion-header crm-master-accordion-header">{$editTitle}</div><!-- /.crm-accordion-header -->
            <div class="crm-accordion-body">
                <table class="form-layout">
                    <tbody>
                    {if $form.only_active}
                        <tr>
                            <td><label for="only_active_rules">{$form.only_active.label}</label></td>
                            <td>{$form.only_active.html}</td>
                        </tr>
                    {/if}
                    </tbody>
                </table>
                <div class="crm-submit-buttons">
                    {include file="CRM/common/formButtons.tpl"}
                </div>
            </div><!- /.crm-accordion-body -->
        </div><!-- /.crm-accordion-wrapper -->
    </div><!-- /.crm-form-block -->

    <br />
    <div class="crm-block">
        <div class="action-link">
            <a class="button" href="{crmURL p="civicrm/dataprocessor/form/edit" q="reset=1&action=add" }">{ts}Add dataprocessor{/ts}</a>
            <a class="button" href="{crmURL p="civicrm/dataprocessor/form/import" q="reset=1&action=add" }">{ts}Import data processor{/ts}</a>
        </div>
    </div>
{/strip}
{/crmScope}

