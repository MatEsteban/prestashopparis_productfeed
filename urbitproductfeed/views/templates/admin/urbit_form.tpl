{*
*
* 2015-2017 Urb-it
*
* NOTICE OF LICENSE
*
*
*
* Do not edit or add to this file if you wish to upgrade Urb-it to newer
* versions in the future. If you wish to customize Urb-it for your
* needs please refer to https://urb-it.com for more information.
*
* @author    Urb-it SA <parissupport@urb-it.com>
* @copyright 2015-2017 Urb-it SA
* @license  http://www.gnu.org/licenses/
*
*
*}

{extends file="helpers/form/form.tpl"}
{block name="input"}

    {if $input.type == 'urbit_additional_attributes'}
        <h2>
            Additional attributes: <button type="button" class="attribute-add btn btn-primary btn-lg">Add</button>
        </h2>
        {assign var=size value=$fields_value[$input.name]|@count + 1}
        <div class="row additional-attributes-table" data-last="{$size}">
            <div class='additional-attributes-item hidden'>
                <div class="well">
                    <div class="row">
                        <div class='block-select-attribute col-lg-2'>
                            <label for="urbit-select-attribute">Name</label>
                            <select class="additional-attributes-item__name" id='urbit-select-attribute'>
                                {foreach $input.options.query AS $option}
                                    {if is_object($option)}
                                        <option value="{$option->$input.options.id}"
                                                {if isset($input.multiple)}
                                                    {foreach $fields_value[$input.name] as $field_value}
                                                        {if $field_value == $option->$input.options.id}
                                                            selected="selected"
                                                        {/if}
                                                    {/foreach}
                                                {else}
                                                    {if $fields_value[$input.name] == $option->$input.options.id}
                                                        selected="selected"
                                                    {/if}
                                                {/if}
                                        >{$option->$input.options.name}</option>
                                    {elseif $option == "-"}
                                        <option value="">-</option>
                                    {else}
                                        <option value="{$option[$input.options.id]}"
                                                {if isset($input.multiple)}
                                                    {foreach $fields_value[$input.name] as $field_value}
                                                        {if $field_value == $option[$input.options.id]}
                                                            selected="selected"
                                                        {/if}
                                                    {/foreach}
                                                {else}
                                                    {if $fields_value[$input.name] == $option[$input.options.id]}
                                                        selected="selected"
                                                    {/if}
                                                {/if}
                                        >{$option[$input.options.name]}</option>
                                    {/if}
                                {/foreach}
                            </select>
                        </div>
                        <div class='block-select-attribute_type col-lg-2 col-lg-offset-1'>
                            <label for="urbit-select-type">Type</label>
                            <select class="additional-attributes-item__type" id="urbit-select-type">
                                <option value='string'>String</option>
                                <option value='number'>Number</option>
                                <option value='boolean'>Boolean</option>
                                <option value='datetimerange'>Datetimerange</option>
                                <option value='float'>Float</option>
                                <option value='text'>Text</option>
                                <option value='time'>Time</option>
                                <option value='url'>URL</option>
                            </select>
                        </div>
                        <div class='block-input-attribute_unit col-lg-2 col-lg-offset-1'>
                            <label for="urbit-input-unit">Unit</label>
                            <input type="text" class="additional-attributes-item__unit" id="urbit-input-unit">
                        </div>
                        <div class="form-group col-lg-3">
                            <button type="button" class="btn btn-danger attribute-remove pull-right btn-lg">Remove</button>
                        </div>
                    </div>
                </div>
            </div>
            {assign var = i value = 1}
            {assign var=name value='name'}
            {assign var=type value='type'}
            {assign var=unit value='unit'}

            {foreach $fields_value[$input.name] as $value}
                <div class='additional-attributes-item'>
                    <div class="well">
                        <div class="row">
                            <div class='block-select-attribute col-lg-2'>
                                <label for="urbit-select-attribute">Name</label>
                                <select name="{$input.name}{"["|cat:$i|cat:"]"}{"["|cat:$name|cat:"]"}"
                                        class="additional-attributes-item__name" id="urbit-select-attribute"
                                        value="{$value[$name]}">
                                    {foreach $input.options.query AS $option}
                                        {if is_object($option)}
                                            <option value="{$option->$input.options.id}"
                                                    {if $value[$name] == $option->$input.options.id}
                                                        selected="selected"
                                                    {/if}
                                            >{$option->$input.options.name}</option>
                                        {elseif $option == "-"}
                                            <option value="">-</option>
                                        {else}
                                            <option value="{$option[$input.options.id]}"
                                                    {if $value[$name] == $option[$input.options.id]}
                                                        selected="selected"
                                                    {/if}
                                            >{$option[$input.options.name]}</option>
                                        {/if}
                                    {/foreach}
                                </select>
                            </div>
                            <div class='block-select-attribute_type col-lg-2 col-lg-offset-1'>
                                <label for="urbit-select-type">Type</label>
                                <select name="{$input.name}{"["|cat:$i|cat:"]"}{"["|cat:$type|cat:"]"}"
                                        class="additional-attributes-item__type" id="urbit-select-type"
                                        value="{$value[$type]}">
                                    {foreach $attribute_types AS $attribute_type}
                                        <option value="{$attribute_type["value"]}"
                                                {if $value[$type] == $attribute_type["value"]}
                                                    selected="selected"
                                                {/if}
                                        >{$attribute_type["name"]}</option>
                                    {/foreach}
                                </select>
                            </div>
                            <div class='block-input-attribute_unit col-lg-2 col-lg-offset-1'>
                                <label for="urbit-input-unit">Unit</label>
                                <input type="text" name="{$input.name}{"["|cat:$i|cat:"]"}{"["|cat:$unit|cat:"]"}"
                                       class="additional-attributes-item__unit" id="urbit-input-unit"
                                       value="{$value[$unit]}">
                            </div>
                            <div class="form-group col-lg-3">
                                <button type="button" class="btn btn-danger attribute-remove pull-right btn-lg">Remove</button>
                            </div>
                        </div>
                    </div>
                </div>
                {assign var = i value=$i + 1}
            {/foreach}
        </div>
        <script>
            (function ($) {
                const $container = $(".additional-attributes-table");

                if ($container.length === 0) {
                    return;
                }

                let i = $container.data("last");
                const $empty = $container.find(".additional-attributes-item.hidden").clone().removeClass("hidden");

                $(document).on("click", ".attribute-add", function () {

                    let $new = $empty.clone().appendTo($container);

                    $new.find(".additional-attributes-item__name").attr("name", "URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW[" + i + "][name]");
                    $new.find(".additional-attributes-item__type").attr("name", "URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW[" + i + "][type]");
                    $new.find(".additional-attributes-item__unit").attr("name", "URBITPRODUCTFEED_ATTRIBUTE_ADDITIONAL_ATTRIBUTE_NEW[" + i + "][unit]");

                    i++;
                });

                $(document).on("click", ".attribute-remove", function () {
                    $(this).closest(".additional-attributes-item").remove();
                });
            })($);
        </script>
    {else}
        {$smarty.block.parent}
    {/if}

{/block}
