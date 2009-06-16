<?php

class Admin_PaymentController extends Zend_Controller_Action{
/* Admin_ : nama folder */
	
	public function indexAction(){
//		$model = $this->_getModel();
//		$this->view->entries = $model->fetchEntries();
		$model = new Kutu_Core_Orm_Table_Order();
		$rows= $model->fetchAll();
		
		$model2 = new Kutu_Core_Orm_Table_Catalog();
		$rows2 = $model2->fetchAll();  
		$this->view->rows= $rows; 		
	}
	
	public function saveAction(){
		$request = $this->getRequest();		
		if($this->getRequest()->isPost()){
			$model = new Kutu_Core_Orm_Table_Order();
			
			/* update*/
			//$row= $model->fetch("id=5")->current(); // tanpa current return rowset 
			$row=$model->find("5")->current(); //primary key only findRow return row
			$row->name='ab';
			$row->save();
			
			/* insert */
			$row=$model->fetchNew();//balikin row empty, lihat di documentation 					
			$row->nama="";
			$row->field=$value;						
			$row->save();
			
			
			
				//return $this->_helper->redirector('index'); 
		}
		$this->view->form = $form ;
	}
	
	public function deleteAction(){
		$model = new Kutu_Core_Orm_Table_Order();
		$row=$model->find(5);
		$row->delete();
	}
	
	protected function _getModel(){
		if(null === $this->_model){
			require_once APPLICATION_PATH.'/models/GuestBook.php';
			$this->_model = new Model_GuestBook();
		}
		return $this->_model;
	}
}