define(['jquery', 'jqueryui'], function($) {
    /*eslint no-console: ["error", { allow: ["log", "warn", "error"] }] */
    return {
        init: function() {
// ---------------------------------------------------------------------------------------------------------------------
            // when a single section is shown under a tab use the section name as tab name
            var changeTab = function(tab, target) {
                console.log('single section in tab: using section name as tab name');

                // if the section is collapsed click it to un-collapse
                target.find('.toggle_closed').click();

                // Replace the tab name with the section name
                var orig_sectionname=target.find('.sectionname:not(.hidden)');
                if($('.tabname_backup:visible').length > -1){
                    var theSectionname = target.attr('aria-label');
                    tab.parent().append(tab.clone().addClass('tabname_backup').hide()); // Create a hidden clone of tab name
                    tab.html(theSectionname).addClass('tabsectionname');

                    // Hide the original sectionname when not in edit mode
                    if($('.inplaceeditable').length === 0) {
                        orig_sectionname.hide();
                        target.find('.sectionhead').hide();
                    } else {
                        orig_sectionname.addClass('edit_only');
                        target.find('.hidden.sectionname').hide();
                        target.find('.section-handle').hide();
                    }
                }
            };

// ---------------------------------------------------------------------------------------------------------------------
            // a section name is updated...
            $(".section").on('updated', function(){
                var new_sectionname = $(this).find('.inplaceeditable').attr('data-value');
                $(this).attr('aria-label', new_sectionname);
                $('.tablink.active').click();
            });

// ---------------------------------------------------------------------------------------------------------------------
            // restore the tab name
            var restore_tab = function(tab) {
                // restore the tab name from the backup
                var the_backup = tab.parent().find('.tabname_backup');
                var the_tab = tab.parent().find('.tabsectionname').removeClass('tabsectionname');
                the_tab.html(the_backup.html());
                the_backup.remove();

                // reveal the original sectionname
//                $('.sectionname').removeClass('edit_only');
//                $('.sectionname').show();
//                $('.sectionhead').show();
//                $('.hidden.sectionname').show();
//                $('.section-handle').show();

                console.log('--> restoring section headline ');
            };

// ---------------------------------------------------------------------------------------------------------------------
            // react to a clicked tab
            var tabClick = function() {$(".tablink").on('click', function() {
                var tabid = $(this).attr('id');
                var sections = $(this).attr('sections');
                var section_array = sections.split(",");

                console.log('----');

                $(this).addClass('active');

                var clicked_tab_name;
                if($(this).find('.inplaceeditable-text')) {
                    clicked_tab_name = $(this).find('.inplaceeditable-text').attr('data-value');
                }
                if (typeof clicked_tab_name == 'undefined') {
                    clicked_tab_name = $(this).html();
                }
                console.log('Clicked tab "'+clicked_tab_name+'":');

                // hide the content of the assessment info block tab
                $('.assessment_info_content').hide();

                $(".active").removeClass("active");
                $(".modulecontent").addClass("active");

                if(tabid === 'tab_all') { // Show all sections
                    $("li.section").show();
                } else if(tabid === 'tab0') { // Show all sections - then hide each section shown in other tabs
                    $("#changenumsections").show();
                    $("li.section").show();
                    $(".topictab").each(function(){
                        if($(this).attr('sections').length > 0) {
                            // if any split sections into an array, loop through it and hide sectoon with the found ID
                            $.each($(this).attr('sections').split(","), function(index, value){
                                var target = $(".section[section-id='"+value+"']");
                                target.hide();
                                if (target.hasClass("hidden")) { // Replace the "hidden" class with "hiding"
                                    target.addClass("hiding");
                                    target.removeClass("hidden");
                                }
//                                console.log("hiding section " + value);
                            });
                        }
                    });
                } else if(tabid === 'tab_assessment_info') { // Show the Assessment Info Block on the main stage
                    console.log('Assessment Info tab clicked!');
                    $("li.section").hide();
                    $("#changenumsections").hide();
                    $("li.section.hidden").addClass("hiding");
                    $("li.section.hiding").removeClass("hidden");

                    $('.assessment_info_content').show();
                } else { // Hide all sections - then show those found in section_array
                    $("#changenumsections").show();
                    $("li.section").hide();
                    // Replace class "hidden" with class "hiding" because the QMUL theme will prevent "hidden" sections
                    // from hiding (don't ask...)
                    $("li.section.hidden").addClass("hiding");
                    $("li.section.hiding").removeClass("hidden");
                    $.each(section_array, function(index, value){
                        var target = $(".section[section-id='"+value+"']");
                        target.show();
                        if (target.hasClass("hiding")) { // Return the "hidden" class if it was "hiding"
                            target.addClass("hidden");
                            target.removeClass("hiding");
                        }
//                        console.log("showing section " + value);
                    });
                }

                // show section-0 when it should be shown always
//                $('.section0_ontop #section-0').show();
                $('#ontop_area #section-0').show();

                var visible_sections=$('li.section:visible').length;
                var visible_stealth_sections=$('li.section.stealth:visible').length;
                var visible_hidden_sections=$('li.section.hidden:visible').length;
                var visible_hiding_sections=$('li.section.hiding:visible').length;
                var visible_blocks = $('#modulecontent').find('.block:visible');
                var no_student_sections = visible_hiding_sections + visible_hidden_sections;

                // if section0 is shown on top do not count it as visible section for the clicked tab
                if ($('.section0_ontop').length > 0) {
                    console.log('section0 is on top - so reducing the number of visible sections for this tab by 1');
                    visible_sections--;
                }

                console.log('number of visible sections: '+visible_sections);
                console.log('number of stealth sections: '+visible_stealth_sections);
                console.log('number of hidden/hiding sections: '+visible_hidden_sections+' / '+visible_hiding_sections);
                console.log('number of visible blocks: '+visible_blocks.length);
                console.log('no student sections: '+no_student_sections);

                if(visible_sections <= no_student_sections && visible_blocks.length === 0) {
                    console.log("This tab contains only hidden sections and will not be shown to students");
                    $(this).addClass('tab-not-shown');

                    // Get the hint string and show the hint icon next to the tab name
                    var self = $(this);
                    require(['core/str'], function(str) {
                        var get_the_string = str.get_string('hidden_tab_hint', 'format_qmultopics');
                        $.when(get_the_string).done(function(theString) {
                            self.find('#not-shown-hint-'+tabid).remove();
                            self.append('<i id="not-shown-hint-'+tabid+'" class="fa fa-info" title="'+theString+'"></i>');
                        });
                    });

                } else {
                    $(this).removeClass('tab-not-shown');
                    $('#not-shown-hint-'+tabid).remove();
                }

                if(visible_sections < 1 && visible_blocks.length === 0) {
                    console.log('tab with no visible sections or blocks - hiding it');
                    $(this).parent().hide();
                    // Now click the 1st visible tab
//                    $('.tablink:visible').first().click();
                } else {
                    console.log('tab with visible sections or blocks - showing it');
                    $(this).parent().show();
                }

                // If option is set and when a tab other than tab 0 shows a single section perform some visual tricks
                var limit = 0;
                var target = $('li.section:visible:not(.hidden)').first();

                // If section0 is shown on top ignore that 1st visible section and use the 2nd section
                if($("#ontop_area").hasClass('section0_ontop')) {
                    limit = 1;
                    target = $('li.section:visible:not(.hidden):eq(1)');
                }
                var first_section_id = target.attr('id');

                if($('.single_section_tab').length  > limit) {
                    if(visible_sections === 1 && first_section_id !== 'section-0' && typeof first_section_id !== 'undefined' &&
                        //                        !$('li.section:visible').first().hasClass('hidden')&&
                        //                        !$('li.section:visible').first().hasClass('stealth')&&
                        $(this).find('.sectionname').html() !== '') {
                        changeTab($(this), target);
                    } else if($("input[name='edit']").val() === 'off' && first_section_id != 'section-0') {
                        restore_tab($(this));
                    }
                }
            });};

// ---------------------------------------------------------------------------------------------------------------------
            // moving a section to a tab by menu
            var tabMove = function () { $(".tab_mover").on('click', function(){
                var tabnum = $(this).attr('tabnr'); // This is the tab number where the section is moved to
                var sectionid = $(this).closest('li.section').attr('section-id');
                var sectionnum = $(this).parent().parent().parent().parent().parent().parent().parent().attr('id').substring(8);
                console.log('--> found section num: '+sectionnum);
                var active_tabid = $('.topictab.active').first().attr('id');

                if(typeof active_tabid == 'undefined') {
                    active_tabid = 'tab0';
                }
                console.log('----');
                console.log('moving section ' + sectionid + ' from tab "' + active_tabid + '" to tab nr '+tabnum);

                // Remove the section id from any tab and add it to the new tab
                $(".tablink").each(function(){
                    $(this).attr('sections', $(this).attr('sections').replace("," + sectionid, ""));
                    $(this).attr('sections', $(this).attr('sections').replace(sectionid + ",", ""));
                    $(this).attr('sections', $(this).attr('sections').replace(sectionid, ""));

                    $(this).attr('section_nums', $(this).attr('section_nums').replace("," + sectionnum, ""));
                    $(this).attr('section_nums', $(this).attr('section_nums').replace(sectionnum + ",", ""));
                    $(this).attr('section_nums', $(this).attr('section_nums').replace(sectionnum, ""));
                });

                if(tabnum > 0) { // No need to store section ids for tab 0
                    if ($("#tab"+tabnum).attr('sections').length === 0) {
                        $("#tab"+tabnum).attr('sections', $("#tab" + tabnum).attr('sections')+sectionid);
                    } else {
                        $("#tab"+tabnum).attr('sections', $("#tab" + tabnum).attr('sections')+","+sectionid);
                    }
                    if ($("#tab"+tabnum).attr('section_nums').length === 0) {
                        $("#tab"+tabnum).attr('section_nums', $("#tab"+tabnum).attr('section_nums')+sectionnum);
                    } else {
                        $("#tab"+tabnum).attr('section_nums', $("#tab" + tabnum).attr('section_nums')+","+sectionnum);
                        console.log('---> section_nums: '+$("#tab" + tabnum).attr('section_nums'));
                    }
                    $("#tab"+tabnum).click();
                    $('#'+active_tabid).click();
                }

                // Restore the tab before moving it in case it was a single
                restore_tab($('#tab'+tabnum));

                // When there is no visible tab hide tab0 and show/click the module content tab
                // and vice versa otherwise...
                var visible_tabs = $(".topictab:visible").length;
                console.log('visible tabs: '+visible_tabs);
                if ($(".topictab:visible").length === 0) {
                    console.log('NO tabs present - showing original content module tab');
                    $("#tab0").fadeOut(200);
                    $(".modulecontentlink").fadeIn(1000); // Ta-daa
                    $(".modulecontentlink").click(); // Activate tab by clicking on it
                } else {
                    console.log('tabs present - hide original content module tab');
                    $(".modulecontentlink").fadeOut(500);
                    $("#tab0").fadeIn(200);

                    // If the last section of a tab was moved click the target tab
                    // otherwise click the active tab to refresh it
                    $('#' + active_tabid).click();
                    var countable_sections = $('li.section:visible').length-($("#ontop_area").hasClass('section0_ontop') ? 1 : 0);
                    if (countable_sections > 0 && $('li.section:visible').length >= countable_sections) {
                        console.log('staying with the current tab (id = '+active_tabid+
                            ') as there are still ' + $('li.section:visible').length+' sections left');
                        $("#tab"+tabnum).click();
                        $('#' + active_tabid).click();
                    } else {
                        console.log('no section in active tab id '+
                            active_tabid + ' left - hiding it and following section to new tab nr '+tabnum);
                        $("#tab"+tabnum).click();
                        $('#' + active_tabid).parent().hide();
                    }
                }
            });};

// ---------------------------------------------------------------------------------------------------------------------
            // moving section0 to the ontop area
            var moveOntop = function() { $(".ontop_mover").on('click', function(){
                var currenttab = $('.tablink.active');
                $("#ontop_area").append($(this).closest('.section'));
                $("#ontop_area").addClass('section0_ontop');
                currenttab.click();
            });};

// ---------------------------------------------------------------------------------------------------------------------
            // moving section0 back into line with others
            var moveInline = function() { $(".inline_mover").on('click', function(){
                var sectionid = $(this).closest('.section').attr('section-id');
                $("#inline_area").append($(this).closest('.section'));
                $('#section-0').show();
                $('#ontop_spacer').hide();
                // Remove the 'section0_ontop' class
                $('.section0_ontop').removeClass('section0_ontop');
                // Find the former tab for section0 if any and click it
                $(".tablink").each(function() {
                    if($(this).attr('sections').indexOf(sectionid) > -1) {
                        $('.tablink').click();
                        $(this).click();
                        return false;
                    }
                });
            });};

// ---------------------------------------------------------------------------------------------------------------------
            // a section edit menu is clicked
            // hide the the current tab from the tab move options of the section edit menu
            // if this is section0 do some extra stuff
            var dropdownToggle = function () { $(".dropdown-toggle").on('click', function(){
                var sectionid = $(this).closest('.section').attr('id');

                // Replace all tabnames with the actual names shown in tabs
                // Get the current tab names
                var tabArray = [];
                var tracknames = []; // Tracking the tab names so to use each only once
                $('.tablink').each(function() {
                    var tabname = '';
                    var trackname = $(this).attr('tab_title');
                    var tabid = $(this).attr('id').substr(3);

                    if ($(this).hasClass('tabsectionname')) {
                        tabname = $(this).html();
                    } else {
                        tabname = $(this).find('.inplaceeditable').attr('data-value');
                    }

                    if($.inArray(trackname,tracknames) < 0) {
                        tabArray[tabid] = tabname;
                        tracknames.push(trackname);
                    }
                });

                // Updating menu options with current tab names as seen on screen
                console.log('--> Updating menu options with current tab names');
                $(this).parent().find('.tab_mover').each(function() {
                    var tabnr = $(this).attr('tabnr');
                    $(this).find('.menu-action-text').html('To Tab "' + tabArray[tabnr] + '"');
                });

                if(typeof $('.tablink.active').attr('id') !== 'undefined') { // If a tab is active exclude it from the menu
                    var tabnum = $('.tablink.active').attr('id').substring(3);
                    $('#'+sectionid+' .tab_mover').show(); // 1st show all options
                    $('#'+sectionid+' .tab_mover[tabnr="'+tabnum+'"]').hide(); // then hide the one not needed
                    console.log('hiding tab ' + tabnum + ' from edit menu for section '+sectionid);
                }
                // If section 0 is shown on top reduce the section edit menu and restore it otherwise
                if (sectionid === 'section-0') {
                    if ($('#ontop_area.section0_ontop').length === 1) { // If section0 is on top don't show tab options
                        $("#section-0 .inline_mover").show();
                        $("#section-0 .tab_mover").addClass('tab_mover_bak').removeClass('tab_mover').hide();
                        $("#section-0 .ontop_mover").hide();
                    } else {
                        $("#section-0 .inline_mover").hide();
                        $("#section-0 .tab_mover_bak").addClass('tab_mover').removeClass('tab_mover_bak').show();
                        $("#section-0 .ontop_mover").show();
                    }
                }
            });};

// ---------------------------------------------------------------------------------------------------------------------
            // Load all required functions
            var initFunctions = function() {
                tabClick();
                tabMove();
                moveOntop();
                moveInline();
                dropdownToggle();
            };

// ---------------------------------------------------------------------------------------------------------------------
            // What to do if a tab has been dropped onto another
            var handleTabDropEvent = function( event, ui ) {
                var dragged_tab = ui.draggable.find('.topictab').first();
                var target_tab = $(this).find('.topictab').first();
                var dragged_tab_id = ui.draggable.find('.topictab').first().attr('id');
                var target_tab_id = $(this).find('.topictab').first().attr('id');
                console.log('The tab with ID "' + dragged_tab_id + '" was dropped onto tab with the ID "' + target_tab_id + '"');
                // Swap both tabs
                var zwischenspeicher = dragged_tab.parent().html();
                dragged_tab.parent().html(target_tab.parent().html());
                target_tab.parent().html(zwischenspeicher);

                // Re-instantiate the just added DOM elements
                initFunctions();

                // Get the new tab sequence and write it back to format options
                var tabSeq = '';
                // Get the id of each tab according to their position (left to right)
                $('.tablink').each(function(){
                    var tabid = $(this).attr('id');
                    if (tabid !== undefined) {
                        if (tabSeq === '') {
                            tabSeq = tabid;
                        } else if (tabSeq.indexOf(tabid) === -1) {
                            tabSeq = tabSeq.concat(',').concat(tabid);
                        }
                    }
                });

                // Get the first section id from the 1st visible tab - this will be used to determine the course ID
                var sectionid = $('.topictab:visible').first().attr('sections').split(',')[0];
                if (sectionid === 'block_assessment_information') {
                    sectionid = $('.topictab:visible:eq(1)').attr('sections').split(',')[0];
                }

                // Finally call php to write the data
                $.ajax({
                    url: "format/qmultopics/ajax/update_tab_seq.php",
                    type: "POST",
                    data: {'sectionid': sectionid, 'tab_seq': tabSeq},
                    success: function(result){
                        console.log('the new tab sequence: ' + result);
                    }});
            };

// ---------------------------------------------------------------------------------------------------------------------
            // Show all sections when the "Module Content" tab is clicked
            $(".modulecontentlink").click(function(){
                // hide the content of the assessment info block tab
                $('.assessment_info_content').hide();
                $("li.section").show();
                $("li.section.hiding").addClass("hidden");
                $("li.section.hidden").removeClass("hiding");
            });

// ---------------------------------------------------------------------------------------------------------------------
            // A link to an URL is clicked - so check if there is a section ID in it and reveal the corresponding tab...
            $("a").click(function(){
                if ($(this).attr('href') !== '#') {
                    var sectionid = $(this).attr('href').split('#')[1];
                    // If the link contains a section ID (e.g. is NOT undefined) click the corresponding tab
                    if (typeof sectionid !== 'undefined') {
                        var sectionnum = $('#'+sectionid).attr('section-id');
                        // Find the tab in which the section is
                        var foundIt = false;
                        $('.tablink').each(function(){
                            if($(this).attr('sections').indexOf(sectionnum) > -1){
                                $(this).click();
                                foundIt = true;
                                return false;
                            }
                        });
                        if (!foundIt) {
                            $('#tab0').click();
                        }
                    }
                }
            });

// ---------------------------------------------------------------------------------------------------------------------
            $(document).ready(function() {
                initFunctions();

                // Force to show the edit menu for section-0
                $("#section-0 .right.side").show();

                // Make tabs draggable
//                if($("input[name='edit']").val() === 'off') { // 'off' actually means 'on' ?!?!
                if($('.tablink .fa-pencil').length > 0) {

                    $('.topictab').parent().draggable({
//                        containment: '.qmultabs', // allow moving only within qmultabs
//                        helper: 'clone', // move a clone
                        cursor: 'move',
                        stack: '.qmultabitem', // make sure the dragged tab is always on top of others
//                        snap: '.qmultabitem',
                        revert: true,
                    });
                    $('.topictab').parent().droppable({
                        accept: '.qmultabitem',
                        hoverClass: 'hovered',
                        drop: handleTabDropEvent,
                    });
                }

                // if the assessment info tab is shown hide the assessment information block and show assessment info tab
                if($('#tab_assessment_info').length > 0) {
                    $( "[sections=block_assessment_information]").parent().show();
                    $('#modulecontent').append($('.block_assessment_information').addClass('assessment_info_content').hide());
                    $('.assessment_info_content').removeClass('d-flex');
                    $('.assessment_info_content').find('.card-body').removeClass('p-3').removeClass('card-body');
                    $('.assessment_info_content').find('.block-inner').removeClass('card');
                    $('.assessment_info_content').find('.show-content').hide();
                    if($('.tablink .fa-pencil').length == 0) { // if NOT in edit mode hide the block header
                        $('.assessment_info_content').find('.card-header').removeClass('d-flex').hide();
                    }
                }
                $('#tab0').click();
                $('.tablink').click();
                // if there is no visible tab show/click the module content tab
                if($(".topictab:visible").length === 0) {
                    $('#tab0').hide();
                    $(".modulecontentlink").show();
                    $(".modulecontentlink").click();
                } else {
                    $(".modulecontentlink").hide();
                    //click all tablinks once to potentially reveal any section names as tab names
                    // click the 1st visible tab by default
                    $('.tablink:visible').first().click();
                }

                // if section0 is on top restrict section menu - restore otherwise
                if($("#ontop_area").hasClass('section0_ontop')) {
                    $("#section-0 .inline_mover").show();
                    $("#section-0 .tab_mover").hide();
                    $("#section-0 .ontop_mover").hide();
                } else {
                    $("#section-0 .inline_mover").hide();
                    $("#section-0 .tab_mover").show();
                    $("#section-0 .ontop_mover").show();
                }
            });
        }
    };
});
