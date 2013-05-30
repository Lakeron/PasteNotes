<?php

/**
 * Homepage presenter.
 */
class ApplicationPresenter extends BasePresenter {

    public function actionDefault() {
        $this->template->data = new ArrayObject();

        if(!$this->code) {
            $this->code = md5($_SERVER['SERVER_ADDR'] . time());
        }

        debug($this);
    }

    public function renderDefault() {
        $this->template->active_tasks = $this->context->model->getNotes($this->code, 1);
        $this->template->done_tasks = $this->context->model->getNotes($this->code, 2);
    }

    protected function createComponentParser(){
        $form = new \Nette\Application\UI\Form();
        $form->getElementPrototype()->class('ajax');

        $form->addTextArea('text', '', 45, 2)
            ->addRule(\Nette\Forms\Form::FILLED, 'Please fill out ToDo list.')
            ->getControlPrototype()->addAttributes(array('class' => 'span12'))
            ->setAttribute('onchange', 'submit()')
            ->setPlaceholder('Paste notes here');

//        $form->addSubmit('save', 'Create');

        $form->onSuccess[] = callback($this, 'parseOnSubmit');

        return $form;
    }

    public function parseOnSubmit(\Nette\Application\UI\Form $form) {
        if($form->getValues()->text) {
            $data = explode("\r\n", $form->getValues()->text);

            foreach($data as $key => $item){
                if(!$item) {
                    unset($data[$key]);
                }
            }

            $this->invalidateControl('active');
            $this->context->model->saveNote($this->code, $data);
        }
    }

    public function handleChangeToActive($note_id) {
        $this->context->model->changeStatus($note_id, 1);
        $this->invalidateControl('active');
        $this->invalidateControl('done');
    }

    public function handleChangeToDone($note_id) {
        $this->context->model->changeStatus($note_id, 2);
        $this->invalidateControl('active');
        $this->invalidateControl('done');
    }

    public function handleChangeToDelete($note_id) {
        $this->context->model->changeStatus($note_id, 3);
        $this->invalidateControl('active');
        $this->invalidateControl('done');
    }
}
