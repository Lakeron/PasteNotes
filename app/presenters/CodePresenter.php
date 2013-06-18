<?php

/**
 * Homepage presenter.
 */
class CodePresenter extends BasePresenter {

    public function beforeRender() {
        $this->setLayout('layout-fluid');
    }

    public function actionDefault() {
        $this->template->data = new ArrayObject();

        if(!$this->code) {
            $this->code = md5($_SERVER['SERVER_ADDR'] . time());
        }
    }

    public function renderDefault() {
        $defaultPools = $this->context->model->getDefaultPools($this->code);
        foreach($defaultPools as $key => &$pool) {
            $pool['webalized_name'] = \Nette\Utils\Strings::webalize($pool->name).$pool->id;
            $pool['tasks'] = $this->context->model->getTasks($this->code, $pool->id);

            if($pool->isActive == 1)
            {
                $this->template->active = $pool;
            } elseif($pool->isDeleted == 1)
            {
                $this->template->deleted = $pool;
                unset($defaultPools[$key]);
            } elseif($pool->isDone == 1)
            {
                $this->template->done = $pool;
            }

        }
        $this->template->defaultPools = $defaultPools;

        $userPools = $this->context->model->getUserPools($this->code);
        foreach($userPools as $key => &$pool) {
            $pool['webalized_name'] = \Nette\Utils\Strings::webalize($pool->name).$pool->id;
            $pool['tasks'] = $this->context->model->getTasks($this->code, $pool->id);
        }
        $this->template->userPools = $userPools;
    }

    protected function createComponentParser(){
        $form = new \Nette\Application\UI\Form();
        $form->getElementPrototype()->class('ajax');

        $form->addTextArea('text', '', 45, 2)
            ->addRule(\Nette\Forms\Form::FILLED, 'Please fill out ToDo list.')
            ->getControlPrototype()->addAttributes(array('class' => 'span12'))
            ->setPlaceholder('Press Enter to add task');

        $form->addSubmit('save', 'Add task')
            ->getControlPrototype()->addAttributes(array('class' => 'span12 btn btn-success'));

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
            $note = $this->context->model->getActive($this->code);
            $this->invalidateControl('defaultPools');
            $this->invalidateControl('userPools');
            $this->invalidateControl('script');
            $this->context->model->saveTask($note->id, $note->pool_id, $data);
        }
    }

    public function handleChangeTask($task_id, $pool_id, $position = null) {
        $this->context->model->changeTask($task_id, $pool_id, $position, $this->code);
        $this->invalidateControl('defaultPools');
        $this->invalidateControl('userPools');
    }

    public function handleUpdatePool($pool_id, $data = array()) {
        $this->context->model->updatePool($pool_id, $this->code, $data);
        $this->invalidateControl('all');
    }

    public function handleDeletePool($pool_id) {
        $this->context->model->deletePool($pool_id, $this->code);
        $this->invalidateControl('all');
    }

    public function handleAddNewPool()
    {
        $this->context->model->addPool($this->code);
        $this->invalidateControl('all');
    }

    public function handleAutoCheck()
    {
        $this->invalidateControl('defaultPools');
        $this->invalidateControl('userPools');
    }
}
