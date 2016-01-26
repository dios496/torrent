function switch_view (view) {
	var views = [];
	$('header > span').each(function () {
		views.push($(this).attr('view'));
		$(this).removeClass('selected');
	})
	$('#app').removeClass(views.join(' '));
	$('#app').addClass(view);
	$('header > span[view=' + view + ']').addClass('selected');

}

function search_category (category) {
	$.ajax({
	 	method: "POST",
	  	url: "actions/torrent_handler.php",
	  	data: { action: 'get_category', category: category },
	  	dataType: "json"
	}).done(function( r ) {
		var torrents = r.torrents;
		load_torrents(torrents);
	});
}

function load_torrents (torrents) {
	$('#search_container').addClass('search');

	$('#search_results').empty();

	$(torrents).each(function () {
		var tor_row = document.createElement('div');
		$(tor_row).addClass('torrent_row').attr('torrent_id', this.id);
		var name = document.createElement('div');
		$(name).addClass('name').html(this.name);
		var download = document.createElement('div');
		$(download).addClass('download').attr('magnet_link', this.magnet_link).html('D');
		var seeds = document.createElement('div');
		$(seeds).addClass('seeds').html(this.seeds);
		var leeches = document.createElement('div');
		$(leeches).addClass('leeches').html(this.leeches);

		$(tor_row).append(download).append(name).append(seeds).append(leeches);

		$('#search_results').append(tor_row);
	});

	$('#search_results .download').click(function () {
		download_torrent($(this).attr('magnet_link'));
	});
}

function download_torrent (magnet_link) {
	if (confirm('Download torrent?')) {
		$.ajax({
		 	method: "POST",
		  	url: "actions/torrent_handler.php",
		  	data: { action: 'download_torrent', magnet_link: magnet_link },
		  	dataType: "json"
		}).done(function( r ) {

		});
	}	
}

function torrent_action (t_action, gid) {
	$.ajax({
	 	method: "POST",
	  	url: "actions/torrent_handler.php",
	  	data: { action: 'torrent_action', gid: gid, t_action: t_action },
	  	dataType: "json"
	}).done(function( r ) {

	});
}

function load_queue () {
	$.ajax({
	 	method: "POST",
	  	url: "actions/torrent_handler.php",
	  	data: { action: 'get_queue'},
	  	dataType: "json"
	}).done(function( r ) {
		$('#queue_container').empty();
		var queue = r.queue;

		$(queue) .each(function () {
		
			var torrent = document.createElement('div');
			$(torrent).addClass('torrent').attr('gid', this.gid);

			var name = document.createElement('span');
			$(name).addClass('name').html(this.name);
			$(torrent).append(name);

			var progress_bar = document.createElement('div');
			var fill = document.createElement('div');
			$(fill).addClass('fill').css('width', this.percent_complete + '%');
			$(progress_bar).append(fill);
			var percent_complete = document.createElement('span');
			$(percent_complete).html(this.percent_complete + '% ' + this.completed_length + ' / ' + this.total_length);
			$(progress_bar).addClass('progress_bar').append(percent_complete);
			$(torrent).append(progress_bar);

			var speed = document.createElement('div');
			$(speed).addClass('speed');
			$(speed).html('D: ' + this.download_speed + '/s U: ' + this.upload_speed + '/s Status: ' + this.status );
			$(torrent).append(speed);

			var remove = document.createElement('div');
			$(remove).addClass('action remove').html('remove').attr('action', 'remove');
			$(torrent).append(remove);

			var pause = document.createElement('div');
			$(pause).addClass('action pause').html('pause').attr('action', 'pause');
			$(torrent).append(pause);

			var unpause = document.createElement('div');
			$(unpause).addClass('action unpause').html('unpause').attr('action', 'unpause');
			$(torrent).append(unpause);

			$('#queue_container').append(torrent);


		});

		$('#queue_container .action').click(function () {
			var gid = $(this).parent().attr('gid');
			var action = $(this).attr('action');
			torrent_action(action, gid);
		}); 
		//console.log(queue);
	});
}

function load_search () {
	$.ajax({
		 	method: "POST",
		  	url: "actions/torrent_handler.php",
		  	data: { action: 'get_browse' },
		  	dataType: "json"
		}).done(function( r ) {
			if (!r.error) {
				var browse = r.browse;
				for (category in browse) {
					var cat_div = document.createElement('div');
					$(cat_div).addClass('category_row');

					var main_cat = document.createElement('div');
					$(main_cat).addClass('main');
					var span = document.createElement('span');
					$(span).attr('category', category).addClass('category').html(browse[category].name);
					$(main_cat).append(span);
					$(cat_div).append(main_cat);
					
					var sub_cats = browse[category].sub_cats;
					var sub_cat_div = document.createElement('div');
					$(sub_cat_div).addClass('sub');
					for (sub_category in sub_cats) {
						var sub_cat = document.createElement('span');
						$(sub_cat).attr('category', sub_category);
						$(sub_cat).addClass('category');
						sub_cat.innerHTML = sub_cats[sub_category].name;
						$(sub_cat_div).append(sub_cat);
					}
					$(cat_div).append(sub_cat_div);

					$('#browse').append(cat_div);
				}
				$('#browse .category').click(function () {
					search_category($(this).attr('category'));
				});
			}
			else if (r.error) {
				$('#browse').html(r.error);
			} 
			else {
				$('#browse').html('Error returning menu. TPB may be down.');
			}
			
		});
}

function set_events () {
	var tabs = $('header > span');
	//console.log(tabs);
	tabs.each(function () {
		$(this).click(function () {
			switch_view($(this).attr('view'));
		});
	});
}

$(document).ready(function () {
	set_events();
	var first_tab = $('header > span')[0];
	var view = $(first_tab).attr('view');
	switch_view(view);

	load_search();

	load_queue();
	setInterval(load_queue, 1000);
});