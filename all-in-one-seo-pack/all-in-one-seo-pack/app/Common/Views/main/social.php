<?php
/**
 * This is the output for social meta on the page.
 *
 * @since 4.0.0
 */
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
// phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect
// phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact

// Set context for meta class to social meta.
$socialMeta = aioseo()->social->output->getMeta();
if ( ! $socialMeta || ! count( $socialMeta ) ) {
	return;
}
?>
<?php
foreach ( $socialMeta as $key => $meta ) :
	if ( ! is_array( $meta ) ) {
		$meta = [ $meta ];
	}
	foreach ( $meta as $m ) :
	?>
		<meta property="<?php echo esc_attr( $key ); ?>" content="<?php echo esc_attr( $m ); ?>" />
<?php
	endforeach;
endforeach;