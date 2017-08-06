<?php
/**
 * @filesource modules/edocument/views/received.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Edocument\Received;

use \Kotchasan\Http\Request;
use \Kotchasan\DataTable;
use \Kotchasan\Date;
use \Kotchasan\Text;
use \Kotchasan\ArrayTool;

/**
 * module=edocument-received
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
  private $sender;

  /**
   * แสดงรายการเอกสารรับ
   *
   * @param Request $request
   * @param array $login
   * @return string
   */
  public function render(Request $request, $login)
  {
    // รายชื่อผู้ส่ง
    $this->sender = \Edocument\Sender\Model::init();
    // URL สำหรับส่งให้ตาราง
    $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
    // ตาราง
    $table = new DataTable(array(
      /* Uri */
      'uri' => $uri,
      /* Model */
      'model' => \Edocument\Received\Model::toDataTable($login),
      /* รายการต่อหน้า */
      'perPage' => $request->cookie('edocument_perPage', 30)->toInt(),
      /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
      'onRow' => array($this, 'onRow'),
      /* คอลัมน์ที่ไม่ต้องแสดงผล */
      'hideColumns' => array('id'),
      /* ตัวเลือกการแสดงผลที่ส่วนหัว */
      'filters' => array(
        'sender_id' => array(
          'name' => 'sender',
          'text' => '{LNG_Sender}',
          'options' => ArrayTool::merge(array(0 => '{LNG_all items}'), $this->sender->toSelect()),
          'default' => 0,
          'value' => $request->request('sender')->toInt()
        ),
      ),
      /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
      'action' => 'index.php/edocument/model/received/action',
      'actionCallback' => 'dataTableActionCallback',
      /* คอลัมน์ที่สามารถค้นหาได้ */
      'searchColumns' => array('topic', 'document_no'),
      /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
      'headers' => array(
        'document_no' => array(
          'text' => '{LNG_Document No.}'
        ),
        'new' => array(
          'text' => '',
          'colspan' => 2
        ),
        'topic' => array(
          'text' => '{LNG_File Name}'
        ),
        'sender_id' => array(
          'text' => '{LNG_Sender}',
          'class' => 'center'
        ),
        'size' => array(
          'text' => '{LNG_size of} {LNG_File}',
          'class' => 'center'
        ),
        'last_update' => array(
          'text' => '{LNG_date}',
          'class' => 'center'
        ),
        'downloads' => array(
          'text' => '{LNG_Download}',
          'class' => 'center'
        )
      ),
      /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
      'cols' => array(
        'new' => array(
          'class' => 'center'
        ),
        'ext' => array(
          'class' => 'center'
        ),
        'sender_id' => array(
          'class' => 'center'
        ),
        'size' => array(
          'class' => 'center'
        ),
        'last_update' => array(
          'class' => 'center nowrap'
        ),
        'downloads' => array(
          'class' => 'center visited'
        )
      ),
      /* ปุ่มแสดงในแต่ละแถว */
      'buttons' => array(
        'detail' => array(
          'class' => 'icon-search button brown notext',
          'id' => ':id',
          'title' => '{LNG_Detail}'
        ),
      ),
    ));
    // save cookie
    setcookie('edocument_perPage', $table->perPage, time() + 3600 * 24 * 365, '/');
    return $table->render();
  }

  /**
   * จัดรูปแบบการแสดงผลในแต่ละแถว
   *
   * @param array $item
   * @return array
   */
  public function onRow($item, $o, $prop)
  {
    if (empty($item['new'])) {
      $item['new'] = '<span class="icon-email-unread color-red notext" title="{LNG_New}"></span>';
    } else {
      $item['new'] = '<span class="icon-email-read color-green notext" title="{LNG_Received}"></span>';
    }
    $item['sender_id'] = $this->sender->get($item['sender_id']);
    $item['topic'] = $item['topic'].'.'.$item['ext'];
    $item['size'] = Text::formatFileSize($item['size']);
    $item['last_update'] = Date::format($item['last_update'], 'd M Y');
    $item['ext'] = '<img src="'.(is_file(ROOT_PATH.'skin/ext/'.$item['ext'].'.png') ? WEB_URL.'skin/ext/'.$item['ext'].'.png' : WEB_URL.'skin/ext/file.png').'" alt="'.$item['ext'].'">';
    return $item;
  }
}
