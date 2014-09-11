<?php
/**
 * Jaxl (Jabber XMPP Library)
 *
 * Copyright (c) 2009-2012, Abhinav Singh <me@abhinavsingh.com>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 * * Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright
 * notice, this list of conditions and the following disclaimer in
 * the documentation and/or other materials provided with the
 * distribution.
 *
 * * Neither the name of Abhinav Singh nor the names of his
 * contributors may be used to endorse or promote products derived
 * from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRIC
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 */

if($argc < 3) {
	echo "Usage: $argv[0] jid pass\n";
	exit;
}

//
// initialize JAXL object with initial config
//
require_once 'jaxl.php';
$client = new JAXL(array(
	'jid' => $argv[1],
	'pass' => $argv[2],
	'log_level' => JAXL_INFO
));

$client->require_xep(array(
	'0060'	// Publish-Subscribe
));

//
// add necessary event callbacks here
//

$client->add_cb('on_auth_success', function() {
	global $client;
	_info("got on_auth_success cb, jid ".$client->full_jid->to_string());

	// create node
	//$client->xeps['0060']->create_node('pubsub.localhost', 'dummy_node');

	// publish
	$item = new JAXLXml('item', null, array('id'=>time()));
	$item->c('entry', 'http://www.w3.org/2005/Atom');

	$item->c('title')->t('Soliloquy')->up();
	$item->c('summary')->t('To be, or not to be: that is the question')->up();
	$item->c('link', null, array('rel'=>'alternate', 'type'=>'text/html', 'href'=>'http://denmark.lit/2003/12/13/atom03'))->up();
	$item->c('id')->t('tag:denmark.lit,2003:entry-32397')->up();
	$item->c('published')->t('2003-12-13T18:30:02Z')->up();
	$item->c('updated')->t('2003-12-13T18:30:02Z')->up();

	$client->xeps['0060']->publish_item('pubsub.localhost', 'dummy_node', $item);
});

$client->add_cb('on_auth_failure', function($reason) {
	global $client;
	$client->send_end_stream();
	_info("got on_auth_failure cb with reason $reason");
});

$client->add_cb('on_disconnect', function() {
	_info("got on_disconnect cb");
});

//
// finally start configured xmpp stream
//
$client->start();
echo "done\n";

?>
