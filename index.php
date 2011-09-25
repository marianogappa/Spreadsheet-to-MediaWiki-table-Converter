<?php
/*
 *  GUI for Spreadsheet to MediaWiki table Converter
 *  Copyright (C) 2011 Mariano Lopez-Gappa (spinetta(at)gmail(dot)com)
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/* Required library for main process */
include 'WikiTable.php';

/* Initialize form variables */
$input              = getPostVariable('input');
$cellStyle          = getPostVariable('cell_style');
$headerColumnStyle  = getPostVariable('header_column_style');
$otherCellStyle     = getPostVariable('other_cell_style');
$otherRowStyle      = getPostVariable('other_row_style');
$headerRowStyle     = getPostVariable('header_row_style');
$rowStyle           = getPostVariable('row_style');
$caption            = getPostVariable('caption');
$tableStyle         = getPostVariable('table_style');
$columnHeader       = getPostCheckboxVariable('column_header');
$rowHeader          = getPostCheckboxVariable('row_header');

$booleanColumnHeader  = $columnHeader == 'checked';
$booleanRowHeader     = $rowHeader == 'checked';

/* Instantiate WikiTable object */
$wikiTable = new WikiTable($input);

/* Set various styling features according to form variables */
$wikiTable->setCellStyle($cellStyle);
$wikiTable->setColumnHeaderStyle($headerColumnStyle);
$wikiTable->setOtherCellStyle($otherCellStyle);
$wikiTable->setOtherRowStyle($otherRowStyle);
$wikiTable->setRowHeaderStyle($headerRowStyle);
$wikiTable->setRowStyle($rowStyle);
$wikiTable->setTableCaption($caption);
$wikiTable->setTableStyle($tableStyle);
$wikiTable->setColumnHeader($booleanColumnHeader);
$wikiTable->setRowHeader($booleanRowHeader);

/* Get the final output table */
$wikiTableOutput = $wikiTable->get();


/* Main output */
$html = <<<HEREDOC
<!doctype html>
<html>
  <head>
    <title>
      Google Spreadsheet/Excel to MediaWiki table format
    </title>
    <style>
      * {font-family: arial, verdana;}
      h1, h2, h6 {
        padding: 0px;
        margin: 3px;
      }
      h6 {
        font-weight: normal;
      }
      .column_holder {
        overflow: auto;
      }
      .column1, .column2 {
        /*display: inline;*/
        float: left;
        margin-right: 30px;
      }
      form {
        display: inline-block;
        padding: 10px 5px;
        margin-top: 20px;
        border: 1px solid #AAA;
        background-color: #F9F9F9;
      }
      pre {
        border: 1px solid #AAA;
        background-color: #F9F9F9;
        display: inline-block;
        padding: 10px 5px;
        font-family: courier, arial, verdana;
      }
      h2 {
        margin-top: 20px;
      }
      input[type=text] {
        width: 100%;
        margin-bottom: 10px;
      }
      input[type=submit] {
        font-size: 20px;
        font-weight: bolder;
        width: 100%;
      }
      a {
        color: grey;
        text-decoration: none;
        margin-right: 15px;
      }
      a:hover {
        text-decoration: underline;
      }
      .footer {
        font-size: 12px;
        color: grey;
      }
    </style>
  </head>
  <body>
    <h1>Google Spreadsheet/Excel to MediaWiki table format</h1>
    <form action='?' method='post'>
      <div class="column_holder">
        <div class="column1">
          <h6><strong>Paste the input table HERE</strong></h6>
          <textarea name='input' rows='25' cols='70'>{$input}</textarea>
          <br/>
          <input type='submit' value="GO!" />
        </div>
        <div class="column2">
          <div class="column_holder">
            <div class="column1">
              <h6>First column header</h6>
              <input type='checkbox' name='column_header' {$columnHeader}/>
            </div>
            <div class="column2">
              <h6>First row header</h6>
              <input type='checkbox' name='row_header' {$rowHeader}/>
            </div>
          </div>
          <h6>Table caption (leave empty for no caption)</h6>
          <input type='text' name='caption' value='{$caption}'/>
          <h6>Table style (i.e. <strong>&lt;table *HERE*&gt;...&lt;/table&gt;</strong>)</h6>
          <input type='text' name='table_style' value="{$tableStyle}"/>
          <h6>Row style (i.e. <strong>&lt;tr *HERE*&gt;...&lt;/tr&gt;</strong>)</h6>
          <input type='text' name='row_style'  value='{$rowStyle}'/>
          <h6>Cell style (i.e. <strong>&lt;td *HERE*&gt;...&lt;/td&gt;</strong>)</h6>
          <input type='text' name='cell_style' value='{$cellStyle}' />
          <h6>Every other row style (optional for even-odd patterns)</h6>
          <input type='text' name='other_row_style' value='{$otherRowStyle}' />
          <h6>Every other cell style (optional for even-odd patterns)</h6>
          <input type='text' name='other_cell_style' value='{$otherCellStyle}' />
          <h6>Cell style for header column</h6>
          <input type='text' name='header_column_style' value='{$headerColumnStyle}' />
          <h6>Cell style for header row</h6>
          <input type='text' name='header_row_style' value='{$headerRowStyle}' />
        </div>
      </div>

    </form>
    <h2>Resulting source code:</h2>
    <pre>{$wikiTableOutput}</pre>
    <div class="footer">
    <a href='?'>Source code</a>
    <a href='http://www.linkedin.com/profile/view?id=116781764'>Mariano Lopez-Gappa</a>
    spinetta(at)gmail(dot)com
    </div>
  </body>
</html>
HEREDOC;


/* Outputs the GUI Screen*/
echo $html;


/**
 * Determines the correct value for each form value.
 * Priority: (1) $_POST (2) Default (3) Empty
 *
 * @staticvar array $defaults
 * @param string $variable
 * @return string
 */
function getPostVariable($variable) {
  static $defaults = array(
    'cell_style'          => '',
    'header_column_style' => '',
    'other_cell_style'    => '',
    'other_row_style'     => '',
    'header_row_style'    => '',
    'row_style'           => '',
    'caption'             => '',
    'table_style'         => 'class=\'wikitable\'',
    'input'               => '',
  );

  if(isset($_POST[$variable]) && !empty($_POST[$variable]))
    return $_POST[$variable];
  elseif(isset($defaults[$variable]))
    return $defaults[$variable];
  else
    return '';
}

/**
 * SPECIAL CASE FOR CHECKBOXES, since they behave differently.
 * Determines the correct value for each form value.
 * Priority: (1) $_POST (2) Default (3) Empty
 *
 * @staticvar array $defaults
 * @param string $variable
 * @return string
 */
function getPostCheckboxVariable($variable) {
  static $defaults = array(
    'column_header'       => 'checked',
    'row_header'          => '',
  );

  if(empty($_POST) && isset($defaults[$variable]))
    return $defaults[$variable];
  elseif(!empty($_POST) && !isset($_POST[$variable]) || empty($_POST) && !isset($defaults[$variable]))
    return '';
  else
    return 'checked';
}
