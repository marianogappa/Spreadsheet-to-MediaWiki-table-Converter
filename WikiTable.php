<?php
/*
 *  Spreadsheet to MediaWiki table Converter
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

/**
 * DESCRIPTION:
 *
 * This class constructs a MediaWiki-format table from an Excel/GoogleDoc
 * copy&paste. It provides a variety of methods to modify the style.
 * It defaults to a Wikipedia styling with first column header.
 *
 * USAGE:
 *
 * (1) $wikiTable = new WikiTable($rawCopyPasteString);
 * (2) ... adequate styling with $wikiTable->setXXX() methods ...
 * (3) $finalWikiTableString = $wikiTable->get();
 *
 * FEATURES:
 *
 * - ENABLING/DISABLING ROW/COLUMN HEADERS
 *
 *     public function setColumnHeader($columnHeader)
 *     public function setRowHeader($rowHeader)
 *
 * - SETTING A TABLE CAPTION
 *
 *     public function setTableCaption($tableCaption)
 *
 * - STYLING
 *
 *     public function setTableStyle($tableStyle)
 *     public function setColumnHeaderStyle($columnHeaderStyle)
 *     public function setRowHeaderStyle($rowHeaderStyle)
 *     public function setRowStyle($rowStyle)
 *     public function setCellStyle($cellStyle)
 *     public function setOtherCellStyle($otherCellStyle)
 *     public function setOtherRowStyle($otherRowStyle)
 *
 *
 * @author Mariano Lopez-Gappa
 * spinetta(at)gmail(dot)com
 */
class WikiTable {
  /**
   * The initial array created from the raw Excel input
   * @var array
   */
  private $rawArray             = array();

  /**
   * Does the output table have a first column header?
   * @var boolean
   */
  private $columnHeader         = true;

  /**
   * Does the output table have a first row header?
   * @var boolean
   */
  private $rowHeader            = false;

  /**
   * The table's caption (which appears above the table)
   * @var
   */
  private $tableCaption         = '';

  /**
   * The table's style (defaults to wikitable class)
   * @var string
   */
  private $tableStyle           = 'class=\'wikitable\'';

  /**
   * The first column header's style
   * @var string
   */
  private $columnHeaderStyle    = '';

  /**
   * The first row header's style
   * @var string
   */
  private $rowHeaderStyle       = '';

  /**
   * The style applying to all normal rows
   * @var string
   */
  private $rowStyle             = '';

  /**
   * The style applying to all normal cells
   * @var string
   */
  private $cellStyle            = '';

  /**
   * When non-empty, overrides cell style for every other cell
   * @var string
   */
  private $otherCellStyle       = '';

  /**
   * When non-empty, overrides row style for every other row
   * @var string
   */
  private $otherRowStyle        = '';

  // ---------------------------------------------------------------------
  /**
   * Class constructor. Recieves the raw Excel input string and converts it
   * to an initial array.
   *
   * @param string $rawExcelArray
   */
  public function __construct($rawExcelArray = '') {
    $this->rawArray = self::excelToArray($rawExcelArray);
  }

  // ---------------------------------------------------------------------
  /**
   * Final output method. Constructs the MediaWiki table string from the initial
   * input and subsequent styling modifications.
   * @return string
   */
  public function get() {
    $wiki = $this->getWikiHeader();

    if($this->validArray()) {
      foreach($this->rawArray as $index => $row) {
        $wiki .= $this->getRow($index, $row);
      }
    }

    $wiki .= $this->getWikiFooter();

    return $wiki;
  }

  // ---------------------------------------------------------------------
  /**
   * PRIVATE METHOD. Converts each row to MediaWiki table format.
   *
   * @param int $index
   * @param array $row
   * @return string
   */
  private function getRow($index, $row) {
    if($index == 0 && $this->hasColumnHeader())
      return $this->getHeader($row);

    $string = "|- ";
    if($this->getRowStyle($index))
      $string .= $this->getRowStyle($index);
    $string .= "\n";

    foreach($row as $i => $cell)
      $string .= $this->getCell($i, $cell);

    return $string;
  }

  // ---------------------------------------------------------------------
  /**
   * PRIVATE METHOD. Converts each cell to MediaWiki table format.
   *
   * @param int $index
   * @param string $cell
   * @return string
   */
  private function getCell($index, $cell) {
    if($index == 0 && $this->hasRowHeader())
      return $this->getHeaderCell($cell);

    $string = "|";

    if($this->getCellStyle($index))
      $string .= $this->getCellStyle($index) . "|";

    $string .= "{$cell}\n";

    return $string;
  }

  // ---------------------------------------------------------------------
  /**
   * PRIVATE METHOD. Converts first row header cell to MediaWiki table format.
   * 
   * @param string $cell
   * @return string
   */
  private function getHeaderCell($cell) {
    $string = "!";

    if($this->rowHeaderStyle)
      $string .= $this->rowHeaderStyle . "|";

    $string .= "{$cell}\n";

    return $string;
  }

  // ---------------------------------------------------------------------
  /**
   * PRIVATE METHOD. Determine correct style for the given cell.
   * 
   * @param int $index
   * @return string
   */
  private function getCellStyle($index) {
    if($index % 2 && $this->hasOtherCellStyle())
      return $this->otherCellStyle;

    return $this->cellStyle;
  }

  // ---------------------------------------------------------------------
  /**
   * PRIVATE METHOD. Determine correct style for the given row.
   *
   * @param int $index
   * @return string
   */
  private function getRowStyle($index) {
    if($index % 2 && $this->hasOtherRowStyle())
      return $this->otherRowStyle;

    return $this->rowStyle;
  }

  // ---------------------------------------------------------------------
  /**
   * PRIVATE METHOD. Converts first column header to MediaWiki table format.
   * 
   * @param array $row
   * @return string
   */
  private function getHeader($row) {
    $header = "|-\n";
    foreach($row as $cell) {
      $header .= "!";
      if($this->columnHeaderStyle)
        $header .= $this->columnHeaderStyle . "|";
      $header .= "{$cell}\n";
    }
    return $header;
  }

  // ---------------------------------------------------------------------
  /**
   * PRIVATE METHOD. Returns the table's header, along with main style and caption.
   *
   * @return string
   */
  private function getWikiHeader() {
    $header = "{|";

    if($this->hasTableStyle())
      $header .= $this->tableStyle;

    $header .= "\n";

    if($this->hasTableCaption())
      $header .= "|+" . $this->tableCaption . "\n";

    return $header;
  }

  // ---------------------------------------------------------------------
  /**
   * PRIVATE METHOD. Returns the table's footer.
   *
   * @return string
   */
  private function getWikiFooter() {
    return "|}";
  }

  // ---------------------------------------------------------------------
  /**
   * PRIVATE METHOD. Validates the input table.
   * 
   * @return boolean
   */
  private function validArray() {
    return is_array($this->rawArray) && isset($this->rawArray[0]) && is_array($this->rawArray[0]) && count($this->rawArray[0]);
  }

  // ---------------------------------------------------------------------
  /**
   * PRIVATE METHOD. Creates an array from the initial input.
   *
   * @param string $excel
   * @return array
   */
  private static function excelToArray($excel) {
    $array = explode("\n", $excel);
    foreach ($array as $index => $row)
      $array[$index] = explode("\t", $row);

    /* Table cells require trimming */
    foreach($array as $i => $row)
      foreach($row as $j => $cell)
        $array[$i][$j] = trim($array[$i][$j]);

    return $array;
  }

  // ---------------------------------------------------------------------
  /**
   * Sets the main style for the table (i.e. <table *HERE*>...</table>)
   *
   * @param string $tableStyle
   */
  public function setTableStyle($tableStyle) {
    $this->tableStyle = $tableStyle;
  }

  // ---------------------------------------------------------------------
  /**
   * Enables/Disables the first row columns header.
   *
   * @param boolean $columnHeader
   */
  public function setColumnHeader($columnHeader) {
    $this->columnHeader = $columnHeader;
  }

  // ---------------------------------------------------------------------
  /**
   * Enables/Disables the first column rows header.
   *
   * @param boolean $rowHeader
   */
  public function setRowHeader($rowHeader) {
    $this->rowHeader = $rowHeader;
  }

  // ---------------------------------------------------------------------
  /**
   * Sets the table caption text. Empty text means no caption. Table caption
   * appears above the table.
   *
   * @param string $tableCaption
   */
  public function setTableCaption($tableCaption) {
    $this->tableCaption = $tableCaption;
  }

  // ---------------------------------------------------------------------
  /**
   * Sets the first column header cell style (i.e. each <th *HERE*>...</th>)
   *
   * @param string $columnHeaderStyle
   */
  public function setColumnHeaderStyle($columnHeaderStyle) {
    $this->columnHeaderStyle = $columnHeaderStyle;
  }

  // ---------------------------------------------------------------------
  /**
   * Sets the first row header cell style (i.e. each <th *HERE*>...</th>)
   *
   * @param string $rowHeaderStyle
   */
  public function setRowHeaderStyle($rowHeaderStyle) {
    $this->rowHeaderStyle = $rowHeaderStyle;
  }

  // ---------------------------------------------------------------------
  /**
   * Sets the row's style. (i.e. each <tr *HERE*>...</tr>)
   * To set an even-odd pattern, use setOtherRowStyle() also.
   *
   * @param string $rowStyle
   */
  public function setRowStyle($rowStyle) {
    $this->rowStyle = $rowStyle;
  }

  // ---------------------------------------------------------------------
  /**
   * Sets the cell's style. (i.e. each <td *HERE*>...</td>)
   * To set an even-odd pattern, use setOtherCellStyle() also.
   *
   * @param string $cellStyle
   */
  public function setCellStyle($cellStyle) {
    $this->cellStyle = $cellStyle;
  }

  // ---------------------------------------------------------------------
  /**
   * Sets every other cell's style. It is used for even-odd patterns.
   *
   * @param string $otherCellStyle
   */
  public function setOtherCellStyle($otherCellStyle) {
    $this->otherCellStyle = $otherCellStyle;
  }

  // ---------------------------------------------------------------------
  /**
   * Sets every other row's style. It is used for even-odd patterns.
   *
   * @param string $otherRowStyle
   */
  public function setOtherRowStyle($otherRowStyle) {
    $this->otherRowStyle = $otherRowStyle;
  }

  // ---------------------------------------------------------------------
  private function hasTableStyle() {
    return $this->tableStyle;
  }

  // ---------------------------------------------------------------------
  private function hasTableCaption() {
    return $this->tableCaption;
  }

  // ---------------------------------------------------------------------
  private function hasRowHeader() {
    return $this->rowHeader;
  }

  // ---------------------------------------------------------------------
  private function hasColumnHeader() {
    return $this->columnHeader;
  }

  // ---------------------------------------------------------------------
  private function hasOtherCellStyle() {
    return $this->otherCellStyle;
  }

  // ---------------------------------------------------------------------
  private function hasOtherRowStyle() {
    return $this->otherRowStyle;
  }
}