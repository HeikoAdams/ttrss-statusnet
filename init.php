<?php
class statusnet extends Plugin {
	private $host;

	function init($host) {
		$this->host = $host;

		$host->add_hook($host::HOOK_ARTICLE_BUTTON, $this);
		$host->add_hook($host::HOOK_PREFS_TAB, $this);
	}

	function about() {
		return array(1.0,
			"Share articles on StatusNet/GNUSocial sites",
			"Heiko Adams");
	}

	function save() {
		$status_url = db_escape_string($_POST["status_url"]);
		$this->host->set($this, "status_url", $status_url);

		$status_type = $_POST["status_type"];
		$this->host->set($this, "status_type", $status_type);

		$status_nameurl = $_POST["status_nameurl"];
		$this->host->set($this, "status_nameurl", $status_nameurl);
		echo "Status.net/GNUSocial settings saved<br/>";
	}

	function get_js() {
		return file_get_contents(dirname(__FILE__) . "/statusnet.js");
	}

	function hook_article_button($line) {
		$article_id = $line["id"];

		$rv = "<img src=\"".basename(dirname(__DIR__))."/statusnet/statusnet.png\"
			class='tagsPic' style=\"cursor : pointer\"
			onclick=\"shareArticleTostatusnet($article_id)\"
			title='".__('Share on StatusNet/GNUSocial')."'>";

		return $rv;
	}

	function getInfo() {
		$id = db_escape_string($_REQUEST['id']);

		$result = db_query("SELECT title, link
				FROM ttrss_entries, ttrss_user_entries
				WHERE id = '$id' AND ref_id = id AND owner_uid = " .$_SESSION['uid']);

		if (db_num_rows($result) != 0) {
			$title = truncate_string(strip_tags(db_fetch_result($result, 0, 'title')),
				100, '...');
			$article_link = db_fetch_result($result, 0, 'link');
		}

		$status_url = $this->host->get($this, "status_url");
		$status_type = $this->host->get($this, "status_type");
		$status_nameurl = $this->host->get($this, "status_nameurl");

		print json_encode(array("title" => $title, "link" => $article_link,
			"id" => $id, "status_url" => $status_url, "status_type" => $status_type,
			"status_nameurl" => $status_nameurl));
	}

	function hook_prefs_tab($args) {
		if ($args != "prefPrefs") return;

		print "<div dojoType=\"dijit.layout.AccordionPane\" title=\"".__("StatusNet/GNUSocial settings")."\">";

		print "<br/>";

		$status_url = $this->host->get($this, "status_url");
		$status_type = $this->host->get($this, "status_type");
		$status_nameurl = $this->host->get($this, "status_nameurl");

		if ($status_type == "on"){
			$cbvalue_type = "checked";
		}
		if ($status_nameurl == "on"){
			$cbvalue_nulr = "checked";
		}

		print "<form dojoType=\"dijit.form.Form\">";

		print "<script type=\"dojo/method\" event=\"onSubmit\" args=\"evt\">
			evt.preventDefault();

		if (this.validate()) {
			console.log(dojo.objectToQuery(this.getValues()));
			new Ajax.Request('backend.php', {
				parameters: dojo.objectToQuery(this.getValues()),
				onComplete: function(transport) {
					notify_info(transport.responseText);
				}
			});
		}
		</script>";

		print "<input dojoType=\"dijit.form.TextBox\" style=\"display : none\" name=\"op\" value=\"pluginhandler\">";
		print "<input dojoType=\"dijit.form.TextBox\" style=\"display : none\" name=\"method\" value=\"save\">";
		print "<input dojoType=\"dijit.form.TextBox\" style=\"display : none\" name=\"plugin\" value=\"statusnet\">";
		print "<table width=\"100%\" class=\"prefPrefsList\">";
		print "<tr><td width=\"40%\">".__("StatusNet/GNUSocial URL")."</td>";
		print "<td class=\"prefValue\"><input dojoType=\"dijit.form.ValidationTextBox\" required=\"1\" name=\"status_url\" regExp='^(http|https)://.*' value=\"$status_url\"></td></tr>";
		print "<tr><td width=\"40%\">".__("Status als Lesezeichen posten")."</td>";
		print "<td class=\"prefValue\"><input type=\"checkbox\" $cbvalue_type dojoType=\"dijit.form.CheckBox\" name=\"status_type\" value=\"$status_type\"></td></tr>";
		print "<tr><td width=\"40%\">".__("URL zum Namen des Lesezeichen hinzufügen")."</td>";
		print "<td class=\"prefValue\"><input type=\"checkbox\" $cbvalue_nulr dojoType=\"dijit.form.CheckBox\" name=\"status_nameurl\" value=\"$status_nameurl\"></td></tr>";
		print "</table>";
		print "<p><button dojoType=\"dijit.form.Button\" type=\"submit\">".__("Save")."</button>";

		print "</form>";

		print "</div>"; #pane

	}

	function api_version() {
		return 2;
	}

}
?>
