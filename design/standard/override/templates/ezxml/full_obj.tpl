<o id="{$object.remote_id}" class="{$object.class_identifier}">
<ns>{foreach $object.assigned_nodes as $node}{$node|node_serialize()}{/foreach}</ns>
<as>{foreach $object.data_map as $attribute}<a id="{$attribute.contentclass_attribute_identifier|wash()}" type="{$attribute.data_type_string}"><![CDATA[{$attribute|to_string()}]]></a>{/foreach}</as>
</o>
