<?xml version="1.0" encoding="utf-8"?>
{if $parent_node_id}

{def $parent_node = fetch( 'content', 'node', hash( 'node_id', $parent_node_id ) )
     $nodes = fetch( 'content', 'tree', hash(
			'parent_node_id', $parent_node_id,
			'main_node_only', true(),
			'sort_by'       , array( 'path', true() )
			) )}
<all>
{content_view_gui content_object=$parent_node.object view='ezxml'}
{if $nodes}
	{foreach $nodes as $node}
		{content_view_gui content_object=$node.object view='ezxml'}
	{/foreach}
{/if}
</all>
{/if}