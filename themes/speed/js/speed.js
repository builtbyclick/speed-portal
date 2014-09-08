Backbone.View.prototype.close = function(){
	this.remove();
	this.unbind();
	if (this.onClose){
		this.onClose();
	}
}

var Report = Backbone.Model.extend({
	forView: function () {
		var modes = {
			1: 'Web',
			2: 'SMS',
			3: 'Email',
			4: 'Twitter'
		};

		var context = this.toJSON();
		context.id = context.incident.incidentid;
		context.link = baseURL + "/reports/view/"+context.incident.incidentid;
		context.name = context.incident.incidenttitle;
		context.description = $("<div>"+context.incident.incidentdescription+"</div>").text();
		context.date = moment(context.incident.incidentdate, "YYYY-MM-DD HH:mm:ss");
		context.site = context.incident.sharingsourceid;
		context.source = modes[context.incident.incidentmode];

		context.category = [];
		_.each(context.categories, function (c) { context.category.push(c.category.title); });
		context.category = context.category.join(', ');

		context.thumbnail = false;
		_.each(context.media, function (m) { if (m.type == 1 && ! context.thumbnail) context.thumbnail = m.link_url; });

		// Source Icon
		context.icon = 'icon-globe';
		if (context.incident.incidentmode == 4)
		{
			context.icon = 'icon-twitter';
		}
		else if (context.incident.incidentmode == 3)
		{
			context.icon = 'icon-envelope';
		}
		else if (context.incident.incidentmode == 2)
		{
			context.icon = 'icon-mobile-phone';
		}
		else if (context.incident.incidentmode == 1)
		{
			context.icon = 'icon-globe';
			_.each(context.media, function (m)
			{
				if (m.type == 2) context.icon = 'icon-facetime-video';
				if (m.type == 1 && (context.icon == 'icon-globe' || context.icon == 'icon-file-text')) context.icon = 'icon-camera';
				if (m.type == 4 && context.icon == 'icon-globe') context.icon = 'icon-rss';
			});
		}

		if (context.site && context.site !== 'main')
		{
			context.url = baseURL + '/reports/sharing/view/'+context.id;
		}
		else
		{
			context.url = baseURL + '/reports/view/'+context.id;
		}

		return context;
	}
});


var ReportCollection = Backbone.Collection.extend(
{
	model : Report,
	initialize : function (models, options)
	{

	},
});
/*
 *  Report list view
 */
var ReportListView = Backbone.View.extend(
{
	template : _.template($("#reports-list-template").html()),
	emptyItemTemplate : _.template($("#empty-item-template").html()),
	initialize : function(options) {
		_.bindAll(this, "addReport", "addAll", 'loading', 'saveFilter', 'removeReport');
		this.model.bind('add', this.addReport);
		this.model.bind('remove', this.removeReport);
		this.model.bind('reset', this.addAll);

		this.container = $(options.container);
		this.liveReports = options.liveReports;
	},
	events : {
		'click .paused' : function () { this.liveReports.toggle(); },
		'click .filter-button' : function() { $('#middle').toggleClass('filterOpen'); },
		'submit form' : 'saveFilter',
		'click form input#reset' : function (e) {
			liveReports.filter({ c:null, m:null, mode:null, q:null, sharing:null });
			liveReports.updateFormFromFilter();

			//e.preventDefault();
		}
	},
	render : function() {
		this.$el.html(this.template({}));
		this.reportList = this.$('ul.reports-list');
		if (this.model.length > 0)
		{
			this.addAll();
		}
		else
		{
			this.reportList.append(this.emptyItemTemplate());
		}

		this.container.html(this.el);

		return this;
	},
	addReport : function(report) {
		var view = new ReportListLiView(
		{
			model : report,
			id : 'update_' + report.id,
			parent : this
		});

		this.reportList.append(view.render().el);
		report.view = view;
		// Remove no reports box
		this.$('li.no-reports').remove();
	},
	removeReport : function() {
		// If we have an empty collection, show 'no reports' message
		if (this.model.length == 0)
		{
			this.reportList.empty();
			this.reportList.append(this.emptyItemTemplate());
		}
	},
	addAll : function() {
		this.reportList.empty();
		this.model.each(this.addReport);
	},
	onClose : function() {
		this.model.unbind('add', this.addReport);
		this.model.unbind('reset', this.addAll);
		// Destroy report views
		this.model.each(function(model) { model.view.close() });
	},
	loading : function(show) {
		if (show)
		{
			this.$('.loading').show();
			this.$('li.no-reports').hide();
		}
		else
		{
			this.$('.loading').hide();
			this.$('li.no-reports').hide();
		}
	},
	saveFilter : function(event) {
		filter = $('#latest-updates form').toJSON();

		// Defaults
		filter = _.extend({ c:[], m:[], mode:[], q:null, sharing:null }, filter);

		delete filter.form_auth_token;

		liveReports.filter(filter);

		event.preventDefault();
	}
});

var ReportListLiView = Backbone.View.extend({
	initialize : function(params) {
		_.bindAll(this, "update", "remove", "close", "togglePopup");
		this.model.bind('change', this.update);
		this.model.bind('remove', this.close);
		this.parent = params.parent;

		// Add events to close popups when clicking anywhere outside the popup
		$(document).on('click', function(e) {
			params.parent.$('ul.reports-list li').popover('destroy').removeClass('popped');
		});
		// Stop clicks on popup closing it.
		$('.popover').on('click', function(e) { e.stopPropagation(); });
	},
	tagName : 'li',
	className : 'clearfix',
	events : {
		'click' : 'togglePopup'
	},
	attributes : function () {
		var context = this.model.forView();
		return {
			'data-placement':'right',
			'title' : context.name,
			'data-content' : _.template($("#report-popover-template").html(), context)
		}
	},
	template : _.template($("#report-item-template").html()),
	render : function() {
		var context = this.model.forView();
		this.$el.html(this.template(context));
		return this;
	},
	remove : function() {
		this.$el.popover('destroy');
		this.$el.remove();
	},
	update : function(report) {
		this.render();
	},
	onClose : function() {
		this.model.unbind('change', this.update);
		this.model.unbind('remove', this.remove);
	},
	togglePopup: function (e) {
		// when clicking on an element which has a popover, hide
		// them all except the current one being hovered upon
		var $popover = this.$el;
		if (! $popover.hasClass('popped'))
		{
			this.parent.$('ul.reports-list li').not('#' + $popover.attr('id')).popover('destroy').removeClass('popped');
			$popover.popover({html : 'true', trigger : 'manual'}).popover('show').addClass('popped');
		}
		else
		{
			$popover.popover('destroy').removeClass('popped');
		}

		e.stopPropagation();
	}
});

var liveReports = {
	request : null,
	pollTimeout: null,
	loaded: null,
	// Latest report ID we pulled
	latestid: 0,
	// Update frequence in ms
	delay: 20000,
	// Current filter
	filters: {},

	initialize : function(params) {
		_.bindAll(this, 'fetch', 'startPolling', 'update', 'stopPolling', 'toggle', 'updateFormFromFilter');
		// Deferred object for tracking fetch process
		dfd = $.Deferred();
		this.request = dfd.promise();
		dfd.resolve();

		this.loaded = $.Deferred();

		try {
			var filters = JSON.parse(localStorage.getItem("updateFilters"));
		} catch (e) {
			filters = {};
		}

		filters = _.extend({ c:[], m:[], mode:[] }, filters);
		this.filters = filters;

		// Create collection
		this.reports = new ReportCollection();
		// Render list view
		this.listView = new ReportListView({
			model : this.reports,
			container : '#latest-updates',
			liveReports : this
		});
		this.listView.render();
		this.reports.view = this.listView;

		this.updateFormFromFilter();

		// Show loading text, hide after first load
		this.listView.loading(true);
		liveReports.loaded.done(_.bind(function () {
			this.listView.loading(false);
		}, this));

		params = _.extend({}, params);
		if (typeof params.delay !== 'undefined')
		{
			this.delay == params.delay;
		}
	},

	// Fetch data from the server
	fetch : function() {
		// Clear timeout
		this.clearPollingTimeout();

		// Check if fetch is already running
		if (this.request.state() == 'pending') return this.request;

		// need to handle race conditions so
		// 1. we're never running multiple syncs at once
		//    handled in poll or fetch
		// 2. we can trigger events when a sync finishes
		//    this.fetching.done( somefunc )
		// 3. we can tell if a sync is running now?
		//    this.fetching.state == 'pending'
		// 4. we can say 'sync now or ignore if already syncing'
		//    this.startPolling(0) is pretty close

		// Build request url
		var fetchURL = baseURL + '/api?task=incidents&limit=20';
		//var fetchURL = '/json/index';

		var params = [];
		for (var _key in this.filters) {
			if (this.filters[_key] != null)
			{
				params.push(_key + '=' + this.filters[_key]);
			}
		}

		if (fetchURL.indexOf("?") !== -1) {
			var index = fetchURL.indexOf("?");
			var args = fetchURL.substr(index+1, fetchURL.length).split("&");
			fetchURL = fetchURL.substring(0, index);

			for (var i=0; i<args.length; i++) {
				params.push(args[i]);
			}
		}

		// Update the fetch URL with parameters
		fetchURL += (params.length > 0) ? '?' + params.join('&') : '';

		dfd = $.getJSON(fetchURL)
			.done(this.update)
			.always(_.bind(function () {
				this.startPolling();
				this.loaded.resolve();
			}, this));
		this.request = dfd.promise();

		// Returning promise object when done
		return this.request;
	},

	// Start timer for polling server
	startPolling : function(delay) {
		// Only reset polling if we're not polling already OR delay is passed
		if (this.pollTimeout == null || typeof delay !== 'undefined')
		{
			delay = typeof delay !== 'undefined' ? delay : this.delay;

			// Clear existing timeout
			this.clearPollingTimeout();
			this.pollTimeout = _.delay(this.fetch, delay);

			// Update pause/resume display
			$('#latest-updates .paused .pause').show();
			$('#latest-updates .paused .resume').hide();
		}
	},

	// Clear timer for polling server
	clearPollingTimeout : function() {
		clearTimeout(this.pollTimeout);
		this.pollTimeout = null;
	},

	// Clear timer for polling server
	stopPolling : function() {
		this.clearPollingTimeout();

		// Update pause/resume display
		$('#latest-updates .paused .pause').hide();
		$('#latest-updates .paused .resume').show();
	},

	isPolling : function()
	{
		if (this.pollTimeout == null) return false;
		return true;
	},

	// Set filter
	// @todo save filters in local storage?
	filter: function(filters) {
		if (filters == undefined) {
			throw "Missing filters";
		}

		var hasChanged = false;

		// Overwrite the current set of filters with the new values
		$.each(filters, _.bind(function(filter, value) {
			var currentValue = this.filters[filter];
			if ((currentValue == undefined && currentValue == null) ||
				currentValue !== value) {
				hasChanged = true;
				this.filters[filter] = value;
			}
		}, this));

		localStorage.setItem("updateFilters", JSON.stringify(this.filters));

		// Have the filters changed
		if (hasChanged) {
			this.startPolling(0);
		}
	},

	// Update display with new data
	update: function (data) {
		var context = this;
		if (typeof data.payload != 'undefined')
		{
			if (typeof data.payload.incidents != 'undefined')
			{
				// Set id field
				_.each(data.payload.incidents, function (item, i) { data.payload.incidents[i].id = item.incident.incidentid; });
				// Set up collection
				this.reports.set(data.payload.incidents);
			}
			else
			{
				this.reports.set([]);
			}
		}
	},

	// Toggle polling
	toggle: function (event) {
		if (this.pollTimeout == null) this.startPolling();
		else this.stopPolling();

		event.preventDefault();
	},
	// @todo move to view
	updateFormFromFilter : function () {
		var filters = this.filters

		$('#latest-updates form input').attr('checked', false);


		if (typeof filters.q !== 'undefined' ) $("#latest-updates form #q").val(filters.q);
		//if (typeof filters.q !== 'undefined' ) $("#latest-updates form #q").val(filters.q);
		if (typeof filters.m !== 'undefined' ) {
			_.each(filters.m, function(v) {
				$('#latest-updates form input[name="m[]"][value="'+v+'"]').attr('checked','checked');
			});
		}
		if (typeof filters.mode !== 'undefined' ) {
			_.each(filters.mode, function(v) {
				$('#latest-updates form input[name="mode[]"][value="'+v+'"]').attr('checked','checked');
			});
		}
		if (typeof filters.c !== 'undefined' ) {
			_.each(filters.c, function(v) {
				$('#latest-updates form input[name="c[]"][value="'+v+'"]').attr('checked','checked');
			});
		}
		if (typeof filters.sharing !== 'undefined' ) {
			_.each(filters.sharing, function(v) {
				$('#latest-updates form input[name="sharing[]"][value="'+v+'"]').attr('checked','checked');
			});
		}
	}
};

$(document).ready(function () {
	liveReports.initialize();

	// Bind events
	// @todo only after first load
	// @todo move to view or something
	liveReports.loaded.done(function () {
		var pausedState = false;
		$('#latest-updates').hover(function () {
			$('#latest-updates .paused').show();
			$('#latest-updates .filter-button').show();
			pausedState = liveReports.isPolling();
			liveReports.stopPolling();
		}, function () {
			$('#latest-updates .paused').hide();
			$('#latest-updates .filter-button').hide();
			if (pausedState) liveReports.startPolling();
		});
	});

	liveReports.startPolling(0);
});