<?php

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    /** @var DibiConnection */
    public $db;

    /** @persistent */
    public $code;


    protected function startup()
    {
        parent::startup();

        // vytvoří instanci služby a uloží do vlastnosti presenteru
        $this->db = $this->context->dibi->connection;
        $this->template->robots = 'NOINDEX, NOFOLLOW';
    }
}
