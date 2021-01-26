<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Form
{
    /**
     * @var string
     */
    public $method = 'POST';
    /**
     * @var string
     */
    public $action = '/update.php';
    /**
     * @var array
     */
    public $attributes = [];
    /**
     * @var string
     */
    public $submitText = 'Save';
    /**
     * @var string
     */
    public $submitColor = 'bg-custom';
    /**
     * @var FormField[]
     */
    public $fields = [];

    /**
     * @return Form
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return Form
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return Form
     * @param string $key
     * @param string $val
     */
    public function addAttribute($key, $val)
    {
        $this->attributes[$key] = $val;
        return $this;
    }

    /**
     * @return Form
     * @param string $text
     */
    public function setSubmitText($text)
    {
        $this->submitText = $text;
        return $this;
    }

    /**
     * @return Form
     * @param string $color
     */
    public function setSubmitColor($color)
    {
        $this->submitColor = $color;
        return $this;
    }

    /**
     * @return Form
     * @param string $type
     * @param bool $hidden
     * @param bool $required
     * @param string $label
     * @param string $name
     * @param array|string $default
     * @param array $options
     * @param string $id
     */
    public function addField($type = 'text', $hidden = false, $required = true, $label = '', $name = '', $default = '', $options = [], $id = '')
    {
        $field = new FormField();
        $field->options = $options;
        $this->fields[] = $field->setType($type)
            ->setHidden($hidden)
            ->setRequired($required)
            ->setLabel($label)
            ->setName($name)
            ->setDefault($default)
            ->setId($id);

        return $this;
    }

    /**
     * @return void
     */
    public function render()
    {
        $attrs = '';
        foreach ($this->attributes as $key => $val) {
            $val = escape($val);
            $attrs .= "{$key}=\"{$val}\" ";
        }
        echo "<form action=\"{$this->action}\" method=\"{$this->method}\" {$attrs}>";
        foreach ($this->fields as $f) {
            $f->render();
        }
        echo "<input type=\"submit\" class=\"btn {$this->submitColor}\" value=\"{$this->submitText}\" />";
        echo '</form>';
    }
}

class FormField
{
    /**
     * @var string
     */
    public $type = 'text';
    /**
     * @var bool
     */
    public $hidden = false;
    /**
     * @var bool
     */
    public $required = true;
    /**
     * @var string
     */
    public $label = '';
    /**
     * @var string
     */
    public $name = '';
    /**
     * @var string|array
     */
    public $default = '';
    /**
     * @var array
     */
    public $options = [];
    /**
     * @var array
     */
    public $inputAttributes = [];
    /**
     * @var array
     */
    public $groupAttributes = [];
    /**
     * @var string
     */
    public $id = '';

    /**
     * @return FormField
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return FormField
     * @param bool $val
     */
    public function setHidden($val)
    {
        $this->hidden = $val;
        return $this;
    }

    /**
     * @return FormField
     * @param bool $val
     */
    public function setRequired($val)
    {
        $this->required = $val;
        return $this;
    }

    /**
     * @return FormField
     * @param string $val
     */
    public function setLabel($val)
    {
        $this->label = $val;
        return $this;
    }

    /**
     * @return FormField
     * @param string $val
     */
    public function setName($val)
    {
        $this->name = $val;
        return $this;
    }

    /**
     * @return FormField
     * @param string|array $val
     */
    public function setDefault($val)
    {
        $this->default = $val;
        return $this;
    }

    /**
     * @return FormField
     * @param string $val
     * @param string $text
     */
    public function addOption($val, $text)
    {
        $this->options[$val] = $text;
        return $this;
    }

    /**
     * @return FormField
     * @param string $key
     * @param string $val
     */
    public function addInputAttribute($key, $val)
    {
        $this->inputAttributes[$key] = $val;
        return $this;
    }

    /**
     * @return FormField
     * @param string $key
     * @param string $val
     */
    public function addGroupAttribute($key, $val)
    {
        $this->groupAttributes[$key] = $val;
        return $this;
    }

    /**
     * @return FormField
     * @param string $val
     */
    public function setId($val)
    {
        $this->id = $val;
        return $this;
    }

    /**
     * @return void
     */
    public function render()
    {
        $groupAttrs = '';
        foreach ($this->groupAttributes as $key => $val) {
            $val = escape($val);
            $groupAttrs .= "{$key}=\"{$val}\" ";
        }

        $inputAttrs = '';
        foreach ($this->inputAttributes as $key => $val) {
            $val = escape($val);
            $inputAttrs .= "{$key}=\"{$val}\" ";
        }

        $req = $this->required ? 'required' : '';
        $id = $this->id;
        $label = escape($this->label);
        $name = escape($this->name);

        // TODO: Flight Time Field Format
        switch (strtolower($this->type)) {
            case 'select':
                echo "<div class=\"form-group\" {$groupAttrs}>";
                echo "<label for=\"{$id}\">{$label}</label>";
                echo "<select class=\"form-control\" {$req} {$inputAttrs} name=\"{$name}\" id=\"{$id}\">";
                foreach ($this->options as $oVal => $oName) {
                    $oName = escape($oName);
                    echo "<option value=\"{$oVal}\">{$oName}</option>";
                }
                echo '</select></div>';
                if ($this->default != '') {
                    echo "<script>$('#{$id}').val(\"{$this->default}\")</script>";
                }
                break;
            case 'textarea':
                echo "<div class=\"form-group\" {$groupAttrs}>";
                echo "<label for=\"{$id}\">{$label}</label>";
                echo "<textarea class=\"form-control\" {$req} {$inputAttrs} name=\"{$name}\" id=\"{$id}\">";
                echo escape($this->default);
                echo '</textarea></div>';
                break;
            case 'select-multi':
                $fakeId = uniqid();
                echo "<div class=\"form-group\" {$groupAttrs}>";
                echo "<label for=\"{$fakeId}\">{$label}</label>";
                echo "<select multiple class=\"form-control selectpicker\" data-live-search=\"true\" {$req} {$inputAttrs} id=\"{$fakeId}\">";
                foreach ($this->options as $oVal => $oName) {
                    $oName = escape($oName);
                    echo "<option value=\"{$oVal}\">{$oName}</option>";
                }
                echo '</select></div>';
                echo "<input {$req} hidden name=\"{$name}\" id=\"{$id}\" />";
                echo "<script>
                    $(\"#{$fakeId}\").on('changed.bs.select', function() {
                        var data = $(\"#{$fakeId}\").val();
                        $(\"#{$id}\").val(data.join(','));
                    });
                </script>";
                if ($this->default != [] && $this->default != '') {
                    $df = Json::encode($this->default);
                    echo "<script>$('#{$id}').val({$df})</script>";
                }
                break;
            default:
                if ($this->hidden) {
                    echo "<input type=\"{$this->type}\" hidden {$req} {$inputAttrs} name=\"{$name}\" id=\"{$id}\" value=\"{$this->default}\" />";
                } else {
                    echo "<div class=\"form-group\" {$groupAttrs}>";
                    echo "<label for=\"{$id}\">{$label}</label>";
                    echo "<input type=\"{$this->type}\" class=\"form-control\" {$req} {$inputAttrs} name=\"{$name}\" id=\"{$id}\" value=\"{$this->default}\" />";
                    echo '</div>';
                }
                break;
        }
    }
}
