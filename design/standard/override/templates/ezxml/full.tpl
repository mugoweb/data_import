<o id="{$object.remote_id}">
<ls>{foreach $object.assigned_nodes as $node}<l parent_id="{$node.parent.remote_id}">{$node.remote_id}</l>{/foreach}</ls>
<as>{foreach $object.data_map as $attribute}<a id="{$attribute.contentclass_attribute_identifier|wash()}">{$attribute|to_string()}</a>{/foreach}</as>
</o>