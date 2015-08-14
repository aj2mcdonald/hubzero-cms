<?php defined('JPATH_BASE') or die();

$doc = JFactory::getDocument();
if (!defined('HG_INLINE'))
{
	$doc->setTitle(JText::_('COM_SEARCH'));
}

// Build the querystring from allowed values rather than
// using $_SERVER['QUERY_STRING'] as 'QUERY_STRING' may
// contain potentially dangerous values (e.g., quote marks,
// javascript. etc.)
$qs = array();
if ($terms = $this->req->getTerms())
{
	$qs[] = 'terms=' . $terms;
}
foreach ($this->req->getTags() as $tag)
{
	$qs[] = 'tags[]=' . $tag['id'];
}
if ($domain = $this->req->getDomain())
{
	$qs[] = 'domain=' . $domain;
}
if ($group = $this->req->getGroup())
{
	$qs[] = 'group=' . $group;
}
if ($page = $this->req->getPage())
{
	$qs[] = 'page=' . $page;
}
if ($per = $this->req->getPerPage())
{
	$qs[] = 'per=' . $per;
}
if ($contributors = $this->req->getContributors())
{
	foreach ($contributors as $cont)
	{
		$qs[] = 'contributors[]=' . $cont['id'];
	}
}
if ($timeframe = $this->req->getTimeframe())
{
	$qs[] = 'timeframe=' . $timeframe;
}
$qs = implode('&', $qs);

$this->css('./hubgraph/hubgraph.css')
     ->js('./hubgraph/hubgraph-update.js')
     ->js('./hubgraph/jquery.inview.js');

if (isset($this->results['js'])): ?>
	<script type="text/javascript">
		<?php echo $this->results['js'] ?>
	</script>
<?php endif; ?>
<?php if (isset($this->results['css'])): ?>
	<style type="text/css">
		<?php echo $this->results['css'] ?>
	</style>
<?php endif; ?>

<?php if (!defined('HG_INLINE')): ?>
	<header id="content-header">
		<h2><?php echo JText::_('COM_SEARCH'); ?></h2>
	</header><!-- / #content-header -->
<?php endif; ?>

	<form id="search-form" class="section-inner search" action="<?php echo JRoute::_('index.php?option=com_search'); ?>" method="get">
		<div class="bar">
			<fieldset>
				<input type="text" autocomplete="off" name="terms" class="terms" placeholder="<?php echo Jtext::_('COM_SEARCH_TERMS_PLACEHOLDER'); ?>" value="<?php echo a($this->req->getTerms()) ?>" />
				<a class="clear" href="<?php echo preg_replace('/[?&]+$/', '', $this->base . ($qs ? '?' . ltrim(preg_replace('/&?terms=[^&]*/', '', $qs), '&') : '')) ?>">&#x2716;</a>
				<button class="submit btn" type="submit"><span><?php echo Jtext::_('COM_SEARCH_SEARCH'); ?></span></button>
			</fieldset>
			<ul class="complete">
				<li class="cat users" title="<?php echo Jtext::_('COM_SEARCH_HUBGRAPH_CONTRIBUTORS'); ?>"><ul></ul></li>
				<li class="cat tags" title="<?php echo Jtext::_('COM_SEARCH_HUBGRAPH_TAGS'); ?>"><ul></ul></li>
				<li class="cat orgs" title="<?php echo Jtext::_('COM_SEARCH_HUBGRAPH_ORGANIZATION'); ?>"><ul></ul></li>
				<li class="cat text"><ul></ul></li>
			</ul>
		</div>
		<?php 
		if (isset($this->results['clientDebug'])):
			define('HG_DEBUG', 1);
		endif;

		if (isset($this->results['html'])):
			echo $this->results['html'];
		endif;

		if ($this->results['terms']['autocorrected']):
			$terms = $this->escape($this->req->getTerms());
			foreach ($this->results['terms']['autocorrected'] as $k => $v):
				$terms = preg_replace('#' . preg_quote($k) . '#i', '<strong>' . $v . '</strong>', $terms);
			endforeach;
		elseif ($this->results['terms']['suggested']):
			$terms = $this->escape($this->req->getTerms());
			$rawTerms = $terms;
			foreach ($this->results['terms']['suggested'] as $k => $v):
				$terms    = str_replace($k, '<strong>' . $v . '</strong>', strtolower($terms));
				$rawTerms = str_replace($k, $v, $rawTerms);
			endforeach;
			$link = preg_replace('/\?terms=[^&]*/', 'terms=' . $rawTerms, $qs);
			if ($link[0] != '?'):
				$link = '?' . $link;
			endif;
		endif;

		$view = $this->view('page')
			->set('req', $this->req)
			->set('results', $this->results)
			->set('perPage', $this->perPage)
			->set('domainMap', $this->domainMap);
		if (isset($terms))
		{
			$view->set('terms', $terms);
		}
		$view->display();
		?>
	</form>