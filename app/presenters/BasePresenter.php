<?php

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    /** @var DibiConnection */
    public $db;


    protected function startup()
    {
        parent::startup();

        // vytvoří instanci služby a uloží do vlastnosti presenteru
        $this->db = $this->context->dibi->connection;
    }
}
