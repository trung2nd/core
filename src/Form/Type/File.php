<?php

namespace Terranet\Administrator\Form\Type;

use Form;
use Terranet\Administrator\Form\Element;
use Czim\Paperclip\Attachment\Attachment;

class File extends Element
{
    /**
     * Require file deletion before new upload
     *
     * @var bool
     */
    protected $forceDelete = true;

    protected $attributes = [
        //
    ];

    /**
     * @var Attachment
     */
    protected $value;

    public function render()
    {
        return $this->getOutput() . $this->getInput();
    }

    /**
     * @return string
     */
    protected function getOutput()
    {
        $output = null;

        if ($this->hasFile()) {
            $files = $this->listFiles();

            $output = $files
                . ($this->forceDelete ? $this->detachLink() : '');
        }

        return $output;
    }

    /**
     * @return mixed
     */
    protected function getInput()
    {
        if (!$this->hasFile() || !$this->forceDelete) {
            return Form::file($this->getFormName(), $this->attributes);
        }

        return null;
    }

    /**
     * @return array|string
     */
    protected function listFiles()
    {
        return link_to(
            $this->value()->url('original'),
            $this->value()->originalFilename()
        );
    }

    /**
     * @return bool
     */
    protected function hasFile()
    {
        return is_a($this->value(), Attachment::class) && $this->value()->originalFilename();
    }

    /**
     * @return string
     */
    protected function detachLink()
    {
        return ''
            . '<div style="margin-top: 10px;">'
            . link_to_route('scaffold.delete_attachment', 'Delete file', [
                'module' => app('scaffold.module'),
                'attachment' => $this->getName(),
                'id' => $this->getRepository()->getKey(),
            ], [
                'onclick' => 'return confirm(\'Are you sure?\');',
                'class' => 'btn btn-danger',
                'style' => 'padding: 2px 46px;',
            ])
            . '</div>';
    }

    /**
     * @return mixed
     */
    protected function value()
    {
        if (null === $this->value) {
            $name = $this->getName();

            return $this->value = $this->getRepository()->$name;
        }

        return $this->value;
    }
}
