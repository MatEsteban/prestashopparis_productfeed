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
            Additional attributes:
            <button type="button"
                    class="attribute-add btn btn-primary btn-lg">{l s='Add' mod='urbitproductfeed'}</button>
        </h2>
        {assign var=size value=$fields_value[$input.name]|@count + 1}
        <div class="row additional-attributes-table" data-last="{$size}">
            <div class='additional-attributes-item hidden'>
                <div class="well">
                    <div class="row">
                        <div class='block-select-attribute col-lg-2'>
                            <label for="urbit-select-attribute">{l s='Name' mod='urbitproductfeed'}</label>
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
                            <label for="urbit-select-type">{l s='Type' mod='urbitproductfeed'}</label>
                            <select class="additional-attributes-item__type" id="urbit-select-type">
                                <option value='string'>{l s='String' mod='urbitproductfeed'}</option>
                                <option value='number'>{l s='Number' mod='urbitproductfeed'}</option>
                                <option value='boolean'>{l s='Boolean' mod='urbitproductfeed'}</option>
                                <option value='datetimerange'>{l s='Datetimerange' mod='urbitproductfeed'}</option>
                                <option value='float'>{l s='Float' mod='urbitproductfeed'}</option>
                                <option value='text'>{l s='Text' mod='urbitproductfeed'}</option>
                                <option value='time'>{l s='Time' mod='urbitproductfeed'}</option>
                                <option value='url'>{l s='URL' mod='urbitproductfeed'}</option>
                            </select>
                        </div>
                        <div class='block-input-attribute_unit col-lg-2 col-lg-offset-1'>
                            <label for="urbit-input-unit">{l s='Unit' mod='urbitproductfeed'}</label>
                            <input type="text" class="additional-attributes-item__unit" id="urbit-input-unit">
                        </div>
                        <div class="form-group col-lg-3">
                            <button type="button"
                                    class="btn btn-danger attribute-remove pull-right btn-lg">{l s='Remove' mod='urbitproductfeed'}</button>
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
                                <label for="urbit-select-attribute">{l s='Name' mod='urbitproductfeed'}</label>
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
                                <label for="urbit-select-type">{l s='Type' mod='urbitproductfeed'}</label>
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
                                <label for="urbit-input-unit">{l s='Unit' mod='urbitproductfeed'}</label>
                                <input type="text" name="{$input.name}{"["|cat:$i|cat:"]"}{"["|cat:$unit|cat:"]"}"
                                       class="additional-attributes-item__unit" id="urbit-input-unit"
                                       value="{$value[$unit]}">
                            </div>
                            <div class="form-group col-lg-3">
                                <button type="button"
                                        class="btn btn-danger attribute-remove pull-right btn-lg">{l s='Remove' mod='urbitproductfeed'}</button>
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
    {elseif $input.type == 'urbit_product_id_filter'}
        <input type="hidden" id='controllerUrl' value="{$controllerlink}">
        <div class="row">
            <div class="col-xs-5">
                <select name="from[]" id="search" class="form-control" size="8" multiple="multiple">
                </select>
            </div>

            <div class="col-xs-1">
                <button type="button" id="search_rightAll" class="btn btn-block"><i class="icon-double-angle-right"></i>
                </button>
                <button type="button" id="search_rightSelected" class="btn btn-block"><i
                            class="icon-chevron-sign-right"></i></button>
                <button type="button" id="search_leftSelected" class="btn btn-block"><i
                            class="icon-chevron-sign-left"></i></button>
                <button type="button" id="search_leftAll" class="btn btn-block"><i class="icon-double-angle-left"></i>
                </button>
            </div>

            <div class="col-xs-5">
                <select name="URBITPRODUCTFEED_FILTER_PRODUCT_ID[]" id="search_to" class="form-control" size="8"
                        multiple="multiple"></select>
            </div>
        </div>
        <script>
            (function ($) {

                $('#search').multiselect({
                    search: {
                        left: '<input type="text" name="q" class="form-control" placeholder="Search..." />',
                        right: '<input type="text" name="q" class="form-control" placeholder="Search..." />',
                    },
                    fireSearch: function (value) {
                        return value.length > 3;
                    }
                });

                getConfigOptionsByAjax();

                $('#urbitproductfeed-filter-categories, #urbitproductfeed-filter-tags, #urbitproductfeed-filter-minimal-stock').change(getOptionsByAjax);
                $('#urbitproductfeed-filter-minimal-stock').on('input', getOptionsByAjax);
            })($);

            //get products for left multiselect
            function getOptionsByAjax() {
                var url = $("#controllerUrl").val();

                $.ajax({
                    method: "POST",
                    url: url,
                    data: {
                        ajax: true,
                        categoriesFromAjax: $('#urbitproductfeed-filter-categories').val(),
                        tagsFromAjax: $('#urbitproductfeed-filter-tags').val(),
                        minimalStockFromAjax: $('#urbitproductfeed-filter-minimal-stock').val()
                    },
                    success: function (data) {
                        var rightMultiselectValues = $.map($('#search_to option'), function (e) {
                            return e.value;
                        });

                        var options = jQuery.parseJSON(data);
                        var $leftSelectBox = $("#search");

                        $leftSelectBox.empty();

                        $.each(options, function (key, value) {
                            //remove duplicates
                            if (jQuery.inArray(value.id, rightMultiselectValues) !== -1) {
                                return;
                            }

                            $leftSelectBox.append($("<option></option>")
                                .attr("value", value.id).text(value.name));
                        });
                    }
                });
            }

            //get products for right multiselect from config
            function getConfigOptionsByAjax() {
                var url = $("#controllerUrl").val();

                $.ajax({
                    method: "POST",
                    url: url,
                    data: {
                        ajax: true,
                        configValues: true
                    },
                    success: function (data) {
                        var options = jQuery.parseJSON(data);
                        var $rightSelectBox = $("#search_to");

                        $rightSelectBox.empty();

                        $.each(options, function (key, value) {
                            $rightSelectBox.append($("<option></option>")
                                .attr("value", value.id).text(value.name));
                        });

                        getOptionsByAjax();
                    }
                });
            }

        </script>
    {elseif $input.type == 'urbit_token'}
        <div class="form-group">
            <input type="text" name="URBITPRODUCTFEED_FEED_TOKEN" class="fixed-width-xxl" id="urbit-feed-token" value="{$fields_value[$input.name]}">
        </div>
        <div class="form-group">
            <button type="submit" value="1" name="submitProductfeedModule" class="btn btn-default">Save Token</button>
        </div>
        <div class="form-group">
            <a href="/index.php?fc=module&module=urbitproductfeed&controller=feed&token={$input.token}" id="generate-token-button" class="btn btn-default">Get the feed</a>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}

{/block}
