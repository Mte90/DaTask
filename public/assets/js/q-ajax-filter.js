jQuery(document).ready(function() { 
    var AF_Filter = function (opts) {
        this.init(opts);
    };

    AF_Filter.prototype = {

        selected: function () {

            var self = this,
            arr = this.loop( jQuery('.' + self.selected_filters + ':selected, .' + self.selected_filters + ' input:checked'), 'tax' );
            
            // Join the array with an "&" so we can break it later.
            return arr.join('&');

        },

        loop: function ( node, tax ) {

            // Return an array of selected navigation classes.
            var arr = [];
            node.each(function () {
                if ( jQuery(this).attr("id") === 'searcher' ) {
                    var id = "search="+jQuery("#searcher").val();
                } else {
                    var id = jQuery(this).data( tax );
                }
                if ( id ) arr.push(id);
            });
            return arr;

        },

        filter: function (arr) {

            var self = this;

            // Return all the relevant posts...
            jQuery.ajax({
                
                url: AF_CONFIG['ajaxurl'],
                data: {
                    'action': 		'wpoad-ajax-search',
                    'filters': 		arr,
                    'paged': 		    AF_CONFIG['thisPage'],
                    '_ajax_nonce':      AF_CONFIG['nonce']
                },

                beforeSend: function () {
                    self.loader.fadeIn();
                    self.section.animate({
                        'opacity': .0
                    }, 'slow');
                    jQuery("body.ajax-filter .pagination").hide("slow"); // show pagination ##
                },

                success: function (html) {
                    self.section.empty();
                    self.section.append(html);
                },

                complete: function () {
                    //console.log("ID: "+self.section.attr("id"));
                    jQuery(".ajax-loaded").fadeIn();
                    jQuery('html, body').animate({
                        scrollTop: jQuery(self.section).offset().top -120
                    }, 500);
                    self.section.animate({
                        'opacity': 1
                    }, 'slow');
                    jQuery(".pagination").show("slow"); // show pagination ##
                    self.loader.fadeOut();
                    self.running = false;
                },

                error: function () {}

            });
        },
        
        clicker: function () {

            var self = this;
            
            jQuery('body').on('click', this.links, function (e) {

                if (self.running === false) {

                    self.first = false; // load normally from now ##

                    // Set to true to stop function chaining.
                    self.running = true;

                    // Cache some of the DOM elements for re-use later in the method.
                    var link = jQuery(this),
                        parent = link.parent('li'),
                        relation = link.attr('rel');

                    if (parent.length > 0) {
                        AF_CONFIG['thisPage'] = 1;
                    }

                    if (relation === 'next') {
                        AF_CONFIG['thisPage']++;
                    } else if (relation === 'prev') {
                        AF_CONFIG['thisPage']--;
                    } else if (link.hasClass('pagelink')) {
                        AF_CONFIG['thisPage'] = relation;
                    }

                    self.filter(self.selected());

                }

                e.preventDefault();

            });

            jQuery('body').on('change', this.select, function (e) {

                if (self.running === false) {

                    self.first = false; // load normally from now ##

                    // Set to true to stop function chaining.
                    self.running = true;

                    // Cache some of the DOM elements for re-use later in the method.
                    var link = jQuery(this),
                        parent = link.parent('select'),
                        relation = link.attr('rel');

                    AF_CONFIG['thisPage'] = 1;

                    if (relation === 'next') {
                        AF_CONFIG['thisPage']++;
                    } else if (relation === 'prev') {
                        AF_CONFIG['thisPage']--;
                    } else if (link.hasClass('pagelink')) {
                        AF_CONFIG['thisPage'] = relation;
                    }

                    self.filter(self.selected());

                }

                e.preventDefault();

            });
            
        },
        
        reset: function () {
            
            // remove all other ".no-results" ##
            jQuery(".no-results").remove();
            
            jQuery("body.ajax-filter #ajax-filtered-section").append("<p class='no-results'></p>"); // add msg ##
            jQuery(".no-results").html(wo_js_vars.on_load_text).fadeIn();
            jQuery("body.ajax-filter .ajax-loaded").hide(); // hide all results ##
            jQuery("body.ajax-filter .pagination").hide(); // hide pagination ##
            
            jQuery('html, body').animate({
                scrollTop: jQuery("#ajax-filtered-section").offset().top -120
            }, 500);
            
        },
        
        init: function (opts) {

            // Set up the properties
            this.opts = opts;
            this.running = false;
            this.first = true; // load differently the first time ##
            this.loader = jQuery(this.opts['loader']);
            this.section = jQuery(this.opts['section']);
            this.links = this.opts['links'];
            this.select = this.opts['select'];
            this.selected_filters = this.opts['selected_filters'];

            // Run the methods.
            this.clicker();

        }
        
    };
    
    var af_filter = new AF_Filter({
        'loader': 			'#ajax-loader',
        'section': 			'#ajax-filtered-section',
        'links': 			'.ajax-filter-label, .paginationNav, .pagelink, #go',
        'select': 			'.ajax-select',
        'progbar': 			'#progbar',
        'selected_filters': 'filter-selected'
    });
        
    // toggle placeholder text on search input ##
    var placeholder_search = jQuery('#searcher').attr('placeholder');
    jQuery('#searcher').focus(function(){
        jQuery(this).attr('placeholder','');
    });
    jQuery('#searcher').focusout(function(){
        jQuery(this).attr('placeholder', placeholder_search );
    });
    
    // reset search ##
    jQuery(".reset").click(function(e) {

        // stop default action ##
        e.preventDefault();

        // update search passed variable ##
        search_passed = false;

        // empty search ##
        jQuery("input#searcher").val("");

        // reset all forms ##
        jQuery("#ajax-filters select").each(function(){
            jQuery(this).find('option:first').attr('selected', 'selected'); // select first option ##
            jQuery(this).find("option").show(); // show all options ##
            jQuery(this).prop('selectedIndex',0);
        });
        
        // back to basics ##
        af_filter.reset();
        
    });
    
    jQuery("input#searcher").keypress(function(event) {
        if (event.which === 13) {
            event.preventDefault();
            jQuery('#go')[0].click();
        }
    });
        
});