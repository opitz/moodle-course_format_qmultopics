define(['jquery', 'jqueryui'], function($) {
    /* eslint no-console: ["error", { allow: ["log", "warn", "error"] }] */
    return {
        init: function() {

// ---------------------------------------------------------------------------------------------------------------------
            function insertTabIndex(element) {
                // Inserts the tabindex from any active tab to its visible sections to make sure they will follow
                // directly after the tab when navigating using the TAB key
                var tabtabindex = element.attr('tabindex');
                if (tabtabindex > 0) {
                    $('.section.main:visible').each( function() {
                        $(this).attr('tabindex',tabtabindex);
                    });
                }
            }

// ---------------------------------------------------------------------------------------------------------------------
            function tabnav() {
                // Supporting navigation using the keyboard
                $(document).keyup(function(e) {
                    var code = e.keyCode || e.which;
                    var focused = $(':focus');
                    // When using the TAB key to navigate the page actually click a tab when in focus to reveal its sections
//                    if (code == '9') { // TAB key pressed
//                        if ( typeof focused.attr('id') !== 'undefined' && focused.attr('id').indexOf("tab") > -1) {
//                            focused.click();
//                        }
//                    }
                    if (code == 13) { // ENTER key pressed
                        // Click a focused tab by pressing ENTER
                        if ( typeof focused.attr('id') !== 'undefined' && focused.attr('id').indexOf("tab") > -1) {
                            focused.click();
                        }
                        // Toggle a focused section by pressing ENTER
                        if ( typeof focused.attr('id') !== 'undefined' && focused.attr('id').indexOf("section") > -1) {
                            focused.find('.toggler:visible').click();
                        }
                    }
                });
            }

// ---------------------------------------------------------------------------------------------------------------------
            function add2tab(tabnum, sectionid, sectionnum) {
                // Remove the section id and section number from any tab
                $(".tablink").each(function() {
                    $(this).attr('sections', $(this).attr('sections').replace("," + sectionid, ""));
                    $(this).attr('sections', $(this).attr('sections').replace(sectionid + ",", ""));
                    $(this).attr('sections', $(this).attr('sections').replace(sectionid, ""));

                    $(this).attr('section_nums', $(this).attr('section_nums').replace("," + sectionnum, ""));
                    $(this).attr('section_nums', $(this).attr('section_nums').replace(sectionnum + ",", ""));
                    $(this).attr('section_nums', $(this).attr('section_nums').replace(sectionnum, ""));
                });
                // Add the sectionid to the new tab
                if (tabnum > 0) { // No need to store section ids for tab 0
                    if ($("#tab" + tabnum).attr('sections').length === 0) {
                        $("#tab" + tabnum).attr('sections', $("#tab" + tabnum).attr('sections') + sectionid);
                    } else {
                        $("#tab" + tabnum).attr('sections', $("#tab" + tabnum).attr('sections') + "," + sectionid);
                    }
                    if ($("#tab" + tabnum).attr('section_nums').length === 0) {
                        $("#tab" + tabnum).attr('section_nums', $("#tab" + tabnum).attr('section_nums') + sectionnum);
                    } else {
                        $("#tab" + tabnum).attr('section_nums', $("#tab" + tabnum).attr('section_nums') + "," + sectionnum);
                        // X console.log('---> section_nums: '+$("#tab"+tabnum).attr('section_nums'));
                    }
                }
            }

// ---------------------------------------------------------------------------------------------------------------------
            function save2tab(tabid) {
                // save the new tab data to the database
                var courseid = $('#courseid').attr('courseid');
                $.ajax({
                    url: "format/topics2/ajax/update_tab_settings.php",
                    type: "POST",
                    data: {
                        'courseid': courseid,
                        'tabid': tabid,
                        'sections': $("#" + tabid).attr('sections'),
                        'sectionnums': $("#" + tabid).attr('section_nums')
                    },
                    success: function(result) {
                        if (result !== '') {
                            console.log(result);
                        }
                    }
                });
            }

// ---------------------------------------------------------------------------------------------------------------------
            var set_numsections_cookie = function() {
                $('#changenumsections').on('click', function(){
                    // store the number of current sections in a cookie - so we know how many have been added later
                    var numSections = $('.section.main').length;
                    sessionStorage.setItem('numSections', numSections);
                });
            };

// ---------------------------------------------------------------------------------------------------------------------
            var escapeHtml = function(text) {
                var map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };

                return text.replace(/[&<>"']/g, function(m) {
 return map[m];
});
            };

// ---------------------------------------------------------------------------------------------------------------------
            // When a limit for the tabname is set truncate the name of the given tab to limit
            var truncateTabname = function(tab) {

                if ($('.limittabname').length > 0) {
                    var x = $('.limittabname').attr('value');
                    var orig_tab_title = tab.attr('tab_title');
                    if (orig_tab_title.length > x) {
                        var short_tab_title = orig_tab_title.substr(0, x) + String.fromCharCode(8230);
                        if (tab.hasClass('tabsectionname')) { // A sectionname as tabname
                            tab.html(short_tab_title);
                        } else {
                            if ($('.inplaceeditingon').length === 0) { // Don't do this while editing the tab name
                                if ($('.inplaceeditable').length > 0) { // We are in edit mode...
                                    tab.find('a').html(tab.find('a').html().replace(escapeHtml(orig_tab_title), short_tab_title));
                                } else {
                                    tab.html(tab.html().replace(escapeHtml(orig_tab_title), short_tab_title));
                                }
                            }
                        }
                    }
                }
            };

// ---------------------------------------------------------------------------------------------------------------------
            var truncateAllTabnames = function() {
                if ($('.limittabname').length > 0) {
                    $('.tablink').each(function() {
                        truncateTabname($(this));
                    });
                }
            };

// ---------------------------------------------------------------------------------------------------------------------
            // When a limit for the tabname is set expand the name of the given tab to the original
            var expandTabname = function(tab) {

                if ($('.limittabname').length > 0) {
                    var x = $('.limittabname').attr('value');
                    var orig_tab_title = tab.attr('tab_title');
                    // Console.log('expand => orig = ' + orig_tab_title);

                    if (orig_tab_title.length > x) {
                        var short_tab_title = orig_tab_title.substr(0, x) + String.fromCharCode(8230);
                        // Console.log('       => short = ' + short_tab_title);
                        if (tab.hasClass('tabsectionname')) { // A sectionname as tabname
                            tab.html(orig_tab_title);
                        } else {
                            if ($('.inplaceeditingon').length === 0) { // Don't do this while editing the tab name
                                if ($('.inplaceeditable').length > 0) { // We are in edit mode...

                                    // Make sure that tab-tile matches data-value after the tab title was edited
                                    var dataValue = tab.find('.inplaceeditable').attr('data-value');
                                    if (orig_tab_title !== dataValue) { // They do NOT match so make them
                                        tab.attr('tab_title', dataValue);
                                        orig_tab_title = dataValue;
                                        short_tab_title = orig_tab_title.substr(0, x) + String.fromCharCode(8230);
                                    }

                                    tab.find('a').html(tab.find('a').html().replace(escapeHtml(short_tab_title), orig_tab_title));
                                } else {
                                    tab.html(tab.html().replace(escapeHtml(short_tab_title), orig_tab_title));
                                }
                            }
                        }
                    }
                }
            };

// ---------------------------------------------------------------------------------------------------------------------
            // When a single section is shown under a tab use the section name as tab name
            var changeTab = function(tab, target) {
                console.log('single section in tab: using section name as tab name');

                // Replace the tab name with the section name
                var origSectionname = target.find('.sectionname:not(.hidden)');
                if ($('.tabname_backup:visible').length > -1) {
                    var theSectionname = target.attr('aria-label');
                    tab.parent().append(tab.clone().addClass('tabname_backup').hide()); // Create a hidden clone of tab name
                    tab.html(theSectionname).addClass('tabsectionname');
                    tab.attr('tab_title', theSectionname);

                    // Hide the original sectionname when not in edit mode
                    if ($('.inplaceeditable').length === 0) {
                        origSectionname.hide();
                        target.find('.sectionhead').hide();
                    } else {
                        target.find('.toggler').addClass('toggler_edit_only').hide();
                        origSectionname.addClass('edit_only');
                    }

                }
            };

// ---------------------------------------------------------------------------------------------------------------------
            // A section name is updated...
            $(".section").on('updated', function() {
                var newSectionname = $(this).find('.inplaceeditable').attr('data-value');
                $(this).attr('aria-label', newSectionname);
                $('.tablink.active').click();
            });

// ---------------------------------------------------------------------------------------------------------------------
            var showTabHint = function(tab) {
                var tabid = tab.attr('id');
                tab.addClass('hidden-tab');

                // Get the hint string and show the hint icon next to the tab name
                require(['core/str'], function(str) {
                    var get_the_string = str.get_string('hidden_tab_hint', 'format_qmultopics');
                    $.when(get_the_string).done(function(theString) {
                        tab.find('#not-shown-hint-'+tabid).remove();
                        var theAppendix = '<i id="not-shown-hint-'+tabid+'" class="fa fa-info" title="'+theString+'"></i>';
                        if (tab.attr('sections').split(',').length == 1
                            && $('.single_section_tabs').length > 0) { // If there is a single topic
                            tab.html(tab.html() + ' ' +theAppendix);
                        } else if ($('.tablink .fa-pencil').length > 0) { // When in edit mode ...
                            tab.find('.inplaceeditable').append(theAppendix);
                        } else {
//                                self.append(theAppendix);
                            tab.html(tab.html() + ' ' +theAppendix);
                        }
                    });
                });
            };

// ---------------------------------------------------------------------------------------------------------------------
            var hideTabHint = function(tab) {
                var tabid = tab.attr('id');
                tab.removeClass('hidden-tab');
                $('#not-shown-hint-'+tabid).remove();
            };

// ---------------------------------------------------------------------------------------------------------------------
            // Restore the tab name
            var restoreTab = function(tab) {
                // Restore the tab name from the backup
                var theBackup = tab.parent().find('.tabname_backup');
                var theTab = tab.parent().find('.tabsectionname').removeClass('tabsectionname');
                theTab.html(theBackup.html());
                theBackup.remove();

                // Reveal the original sectionname
                $('.sectionname').removeClass('edit_only').show();

                $('.hidden.sectionname').show();
                $('.section-handle').show();
//                $('.sectionname').find('.toggler').show();
                $('.toggler_edit_only').removeClass('toggler_edit_only').show();

                 console.log('--> restoring section headline ');
            };

// ---------------------------------------------------------------------------------------------------------------------
            // react to a clicked tab
            var tabClick = function() {$(".tablink").on('click', function() {
                var courseid = $('#courseid').attr('courseid');
                var tabid = $(this).attr('id');
                var sections = $(this).attr('sections');
                var sectionArray = sections.split(",");
                // Make this an active tab
                $(".tablink.active").removeClass("active"); // First remove any active class from tabs
                $(this).addClass('active'); // Then add the active class to the clicked tab

                // store the course ID and the ID of the active tab in cookies
                sessionStorage.setItem('courseid', courseid);
                sessionStorage.setItem('tabid', tabid);

                // If the tab titles are limited - limit them and expand only the active
                if ($('.limittabname').length > 0) {
                    truncateAllTabnames();
                    expandTabname($(this));
                }

                var clickedTabName;
                if ($(this).find('.inplaceeditable-text')) {
                    clickedTabName = $(this).find('.inplaceeditable-text').attr('data-value');
                }
                if (typeof clickedTabName == 'undefined') {
                    clickedTabName = $(this).html();
                }
                console.log('=====> Clicked tab "' + clickedTabName + '":');

                // Hide the content of the assessment info block tab
                $('.assessment_info_block_content').hide();

                $(".tablink.active").removeClass("active");
                $(".modulecontent").addClass("active");

                $('#content_assessmentinformation_area').hide();
                $('#assessment_information_summary').hide();
                $('#assessment_information_area').hide();

                if (tabid === 'tab0') { // Show all sections - then hide each section shown in other tabs
                    $("#changenumsections").show();
//                    $("li.section").show();
                    $("li.section").removeClass('hidden').show();
                    $(".topictab:visible").each(function() {
                        if ($(this).attr('sections').length > 0) {
                            // If any split sections into an array, loop through it and hide section with the found ID
                            $.each($(this).attr('sections').split(","), function(index, value) {
                                var target = $(".section[section-id='" + value + "']");
//                                target.hide();
                                target.addClass('hidden').hide();
                                console.log("--> hiding section " + value);
                            });
                        }
                    });
                } else if (tabid === 'tab_assessment_information') { // Show the Assessment Information as new tab
//                    console.log('Assessment Info tab clicked!');
//                    $("li.section").hide();
                    $("li.section").addClass('hidden').hide();
                    $("li.section.hidden").addClass("hiding");
                    $("li.section.hiding").removeClass("hidden");

                    $('#content_assessmentinformation_area').show();
                    if ($('.merge_assessment_info').length > 0) {
//                        console.log('merging Assessment Info Block');
                        $('#assessment_information_summary').show();
                        $('#assessment_information_area').show();
                    }
                } else if (tabid === 'tab_assessment_info_block') { // Show the Assessment Info Block on the main stage
//                    console.log('Assessment Info Block tab clicked!');
//                    $("li.section").hide();
                    $("li.section").addClass('hidden').hide();
                    $("#changenumsections").hide();
                    $("li.section.hidden").addClass("hiding");
                    $("li.section.hiding").removeClass("hidden");

//                    $('.assessment_info_block_content').show();
                    $('#assessment_information_summary').show();
                    $('#assessment_information_area').show();
                } else { // Hide all sections - then show those found in sectionArray
                    $("#changenumsections").show();
//                    $("li.section").hide();
                    $("li.section").addClass('hidden').hide();
                    $.each(sectionArray, function(index, value) {
                        var target = $(".section[section-id='" + value + "']");
//                        target.show();
                        target.removeClass('hidden').show();
                        console.log("--> showing section " + value);
                    });
                }

                // Show section-0 always when it should be shown always
//                $('#ontop_area #section-0').show();
                $('#ontop_area #section-0').removeClass('hidden').show();

                var visibleSections = $('li.section:visible').length;
//                var hiddenSections = $('li.section.hidden:visible').length;
                var hiddenSections = $('li.section:visible').find('.ishidden').length;
                var visibleBlocks = $('#modulecontent').find('.block:visible');
                var visibleAssessmentInfo = $('#content_assessmentinformation_area:visible').length;

                if ($('.section0_ontop').length > 0) {
                    console.log('section0 is on top - so reducing the number of visible sections for this tab by 1');
                    visibleSections--;
                }

                if (visibleSections < 1 && visibleBlocks.length === 0 && visibleAssessmentInfo === 0) {
                    console.log('tab with no visible sections - hiding it');
                    $(this).parent().hide();

                    // Restoring generic tab name
                    var genericTitle = $(this).attr('generic_title');
                    $.ajax({
                        url: "format/topics2/ajax/update_tab_name.php",
                        type: "POST",
                        data: {'courseid': courseid, 'tabid': tabid, 'tab_name': genericTitle},
                        success: function(result) {
                            if (result !== '') {
                                console.log('Reset name of tab ID ' + tabid + ' to "' + result + '"');
                                $('[data-itemid=' + result + ']').attr('data-value', genericTitle).
                                find('.quickeditlink').html(genericTitle);

                                // Re-instantiate the just added DOM elements
                                initFunctions();
                            }
                        }
                    });
                } else {
                    console.log('tab with visible sections - showing it');
                    $(this).parent().show();
                }

                // If option is set and when a tab other than tab 0 shows a single section perform some visual tricks
                if ($('.single_section_tabs').length > 0
                    && $(this).attr('sections').split(',').length == 1
                    && tabid !== 'tab0') {
                    var target = $('li.section:visible').first();

                    // If section0 is shown always on top ignore the first visible section and use the 2nd
                    if ($('.section0_ontop').length > 0) {
                        target = $('li.section:visible:eq(1)');
                    }
                    var firstSectionId = target.attr('id');

                    if (visibleSections - hiddenSections <= 1
                        && firstSectionId !== 'section-0'
                        && $(this).attr('generic_title').indexOf('Tab') >= 0 // Do this only for original tabs
                    ) {
                        changeTab($(this), target);
                        // Make sure the content is un-hidden
                        target.find('.toggle_area').removeClass('hidden').show();
                    } else if ($('.inplaceeditable').length > 0 && firstSectionId !== 'section-0') {
                        restoreTab($(this));
                    }
                }

                // If all visible sections are hidden for students the tab is hidden for them as well
                // in this case mark the tab for admins so they are aware
                if (visibleSections <= hiddenSections && visibleBlocks.length === 0 && visibleAssessmentInfo === 0) {
                    showTabHint($(this));
                } else {
                    hideTabHint($(this));
                }

                // If tab0 is alone hide it
                if (tabid === 'tab0' && $('.tabitem:visible').length === 1) {
                    // X console.log('--> tab0 is a single tab - hiding it');
                    $('.tabitem').hide();
                }
                // this will make sure tab navigation goes from tab to its sections and then on to the next tab
                insertTabIndex($(this));
            });};

// ---------------------------------------------------------------------------------------------------------------------
            // Moving a section to a tab by menu
            var tabMove = function() {
 $(".tab_mover").on('click', function() {
                var tabnum = $(this).attr('tabnr'); // This is the tab number where the section is moved to
                var sectionid = $(this).closest('li.section').attr('section-id');
                var sectionnum = $(this).closest('li.section').attr('id').substring(8);

                // X console.log('--> found section num: '+sectionnum);
                var activeTabId = $('.topictab.active').first().attr('id');

                if (typeof activeTabId == 'undefined') {
                    activeTabId = 'tab0';
                }
                // X console.log('----');
                // X console.log('moving section '+sectionid+' from tab "'+activeTabId+'" to tab nr '+tabnum);

                // Remove the section id and section number from any tab
                $(".tablink").each(function() {
                    $(this).attr('sections', $(this).attr('sections').replace("," + sectionid, ""));
                    $(this).attr('sections', $(this).attr('sections').replace(sectionid + ",", ""));
                    $(this).attr('sections', $(this).attr('sections').replace(sectionid, ""));

                    $(this).attr('section_nums', $(this).attr('section_nums').replace("," + sectionnum, ""));
                    $(this).attr('section_nums', $(this).attr('section_nums').replace(sectionnum + ",", ""));
                    $(this).attr('section_nums', $(this).attr('section_nums').replace(sectionnum, ""));
                });
                // Add the sectionid to the new tab
                if (tabnum > 0) { // No need to store section ids for tab 0
                    if ($("#tab" + tabnum).attr('sections').length === 0) {
                        $("#tab" + tabnum).attr('sections', $("#tab" + tabnum).attr('sections') + sectionid);
                    } else {
                        $("#tab" + tabnum).attr('sections', $("#tab" + tabnum).attr('sections') + "," + sectionid);
                    }
                    if ($("#tab" + tabnum).attr('section_nums').length === 0) {
                        $("#tab" + tabnum).attr('section_nums', $("#tab" + tabnum).attr('section_nums') + sectionnum);
                    } else {
                        $("#tab" + tabnum).attr('section_nums', $("#tab" + tabnum).attr('section_nums') + "," + sectionnum);
                        // X console.log('---> section_nums: '+$("#tab"+tabnum).attr('section_nums'));
                    }
                }
                $("#tab" + tabnum).click();
                $('#' + activeTabId).click();

                // Restore the section before moving it in case it was a single
                restoreTab($('#tab' + tabnum));

                // If the last section of a tab was moved click the target tab
                // otherwise click the active tab to refresh it
                var countableSections = $('li.section:visible').length - ($("#ontop_area").hasClass('section0_ontop') ? 1 : 0);
                // X console.log('---> visible sections = '+$('li.section:visible').length);
                // X console.log('---> countableSections = '+countableSections);
                if (countableSections > 0 && $('li.section:visible').length >= countableSections) {
                    // X console.log('staying with the current tab (id = '+activeTabId+
                    // X   ') as there are still '+$('li.section:visible').length+' sections left');
                    $("#tab" + tabnum).click();
                    $('#' + activeTabId).click();
                } else {
                    // X console.log('no section in active tab id '+
                    // X   activeTabId+' left - hiding it and following section to new tab nr '+tabnum);
                    $("#tab" + tabnum).click();
                    $('#' + activeTabId).parent().hide();
                }
            });
};

// ---------------------------------------------------------------------------------------------------------------------
            // Moving section0 to the ontop area
            var moveOntop = function() {
                $(".ontop_mover").on('click', function() {
                    $("ul#ontop_area").append($(this).closest('.section')).addClass('section0_ontop');
//                    $("#ontop_area").addClass('section0_ontop');
                    $("#section-0").removeClass('main');
                });
            };

// ---------------------------------------------------------------------------------------------------------------------
            // Moving section0 back into line with others
            var moveInline = function() {
                $(".inline_mover").on('click', function() {
                    var sectionid = $(this).closest('.section').attr('section-id');
                    $("#inline_area").append($(this).closest('.section'));
                    $("#section-0").addClass('main');
                    // Remove the 'section0_ontop' class
                    $('.section0_ontop').removeClass('section0_ontop');
                    // Find the former tab for section0 if any and click it
                    $(".tablink").each(function() {
                        if ($(this).attr('sections').indexOf(sectionid) > -1) {
                            $(this).click();
                            return false;
                        }
                        return false;
                    });
                });
            };

// ---------------------------------------------------------------------------------------------------------------------
            // A section edit menu is clicked
            // hide the the current tab from the tab move options of the section edit menu
            // if this is section0 do some extra stuff
            var dropdownToggle = function() {$(".menubar").on('click', function() {
                if ($(this).parent().parent().hasClass('section_action_menu')) {
                    var sectionid = $(this).closest('.section').attr('id');
                    $('#' + sectionid + ' .tab_mover').show(); // 1st show all options
                    // replace all tabnames with the current names shown in tabs
                    // Get the current tab names
                    var tabArray = [];
                    var trackIds = []; // Tracking the tab IDs so to use each only once
                    $('.tablink').each(function() {
                        if (typeof $(this).attr('id') !== 'undefined') {
                            var tabname = '';
                            var tabid = $(this).attr('id').substr(3);
                            if ($(this).hasClass('tabsectionname')) {
                                tabname = $(this).html();
                            } else {
                                tabname = $(this).find('.inplaceeditable').attr('data-value');
                            }
                            if ($.inArray(tabid, trackIds) < 0) {
                                if ($(this).hasClass('hidden-tab')) { // If this is a hidden tab remove all garnish from the name
                                    tabname = $(this).find('a').clone();
                                    tabname.find('span.quickediticon').remove();
                                    tabname = $.trim(tabname.html());
                                }
                                tabArray[tabid] = tabname;
                                trackIds.push(tabid);
                            }
                        }
                    });

                    // Updating menu options with current tab names
//                    console.log('--> Updating menu options with current tab names');
                    $(this).parent().find('.tab_mover').each(function() {
                        var tabnr = $(this).attr('tabnr');
//                        var tabtext = $(this).find('.menu-action-text').html();
//                        console.log(tabnr + ' --> ' + tabtext.trim() + ' ==> ' + tabArray[tabnr]);

                        var newMenuText = 'To Tab ' +
                            (tabArray[tabnr] === '' || tabArray[tabnr] === 'Tab ' + tabnr ? tabnr : '"' + tabArray[tabnr] +
                            ((tabArray[tabnr] === 'Tab ' + tabnr || tabnr === '0') ? '"' : '" (Tab ' + tabnr + ')'));

                        $(this).find('.menu-action-text').html(newMenuText);
                    });
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
                    } else if (typeof $('.tablink.active').attr('id') !== 'undefined') {
                        var tabnum = $('.tablink.active').attr('id').substring(3);
                        $('#' + sectionid + ' .tab_mover[tabnr="' + tabnum + '"]').hide(); // Then hide the one not needed
//                        console.log('hiding tab ' + tabnum + ' from edit menu for section '+sectionid);
                    }
                    if ($('.tablink:visible').length === 0) {
                        $('#' + sectionid + ' .tab_mover[tabnr="0"]').hide();
                    }
                }
            });
};

// ---------------------------------------------------------------------------------------------------------------------
            // a section edit menu is clicked - to hide or show a section to students
            var toggleAvailiability = function() {
                $(".section-actions .menubar .action-menu-trigger .dropdown .dropdown-menu .dropdown-item").on('click', function() {
                    var activeTab = $('.tablink.active');
                    var visibleSections = $('li.section:visible').length;
//                    var hiddenSections = $('li.section.hidden:visible').length;
                    var hiddenSections = $('li.section:visible').find('.ishidden').length;
                    var visibleBlocks = $('#modulecontent').find('.block:visible');
                    var visibleAssessmentInfo = $('#content_assessmentinformation_area:visible').length;

                    if ($(this).find('.menu-action-text').html().indexOf("Hide") >= 0) {
                        if (activeTab.attr('id') != undefined
                            && visibleSections <= hiddenSections + $('.section0_ontop').length + 1
                            && visibleBlocks.length === 0
                            && visibleAssessmentInfo === 0
                        ) {
                            showTabHint(activeTab);
                        }
                    }
                    if ($(this).find('.menu-action-text').html().indexOf("Show") >= 0) {
                        if (activeTab.attr('id') != undefined) {
                            hideTabHint(activeTab);
                        }
                    }
                });};

// ---------------------------------------------------------------------------------------------------------------------
            // A direct URL to a specific section is clicked - reveal the corresponding tab
            var followTabUrl = function() {
                $("a").click(function() {
                    if ($(this).attr('href') !== '#' && typeof $(this).attr('href') !== 'undefined') {
                        var sectionnum = $(this).attr('href').split('#')[1];
                        // If the link contains a sectionnum (e.g. is NOT undefined) find and click the corresponding tab
                        if (typeof sectionnum !== 'undefined') {
                            // Find the corresponding section ID for the section number we found
                            var sectionid = $('#' + sectionnum).attr('section-id');
                            // Find the tab to which the section ID is related
                            var foundIt = false;
                            $('.tablink').each(function() {
                                if ($(this).attr('sections').indexOf(sectionid) > -1) {
                                    $(this).click();
                                    foundIt = true;
                                    return false;
                                }
                            });
                            // If it has not been found related to any tab it has to be under tab0 - so click that
                            if (!foundIt) {
                                $('#tab0').click();
                            }
                        }
                    }
                });};

// ---------------------------------------------------------------------------------------------------------------------
            var initFunctions = function() {
                // Load all required functions above
                tabClick();
                tabMove();
                moveOntop();
                moveInline();
                dropdownToggle();
                set_numsections_cookie();
                tabnav();
                toggleAvailiability();
                followTabUrl();
            };

// ---------------------------------------------------------------------------------------------------------------------
            // what to do if a tab has been dropped onto another
            var handleTabDropEvent = function(event, ui) {
                var course_format_name = $(document).find('.course_format_name').html();
                var draggedTab = ui.draggable.find('.topictab').first();
                var targetTab = $(this).find('.topictab').first();

// For development purposes only - not used in production environments
                var draggedTab_id = draggedTab.attr('id');
                var targetTab_id = targetTab.attr('id');
                console.log('The tab with ID "' + draggedTab_id + '" was dropped onto tab with the ID "' + targetTab_id + '"');

                // Swap both tabs
                var zwischenspeicher = draggedTab.parent().html();
                draggedTab.parent().html(targetTab.parent().html());
                targetTab.parent().html(zwischenspeicher);

                // Re-instantiate the clickability for the just added DOM elements
                initFunctions();

                // Get the new tab sequence and write it back to format options
                var tabSeqA = [];

                // Get the id of each tab according to their position (left to right)
                $('.tablink').each(function() {
                    var tabid = $(this).attr('id');
                    if (typeof tabid !== 'undefined') {
                        if (!tabSeqA.includes(tabid)) {
                            tabSeqA.push(tabid);
                        }
                    }
                });

                // Implode the array to a string
                var tabSeq = tabSeqA.join();

                // Finally call php to write the data
                var courseid = $('#courseid').attr('courseid');
                $.ajax({
                    url: "format/topics2/ajax/update_tab_seq.php",
                    type: "POST",
                    data: {'courseid': courseid, 'tab_seq': tabSeq, 'course_format_name': course_format_name},
//                    Success: function() {
                    success: function(result) {
                        console.log('the new tab sequence: ' + result);
                    }});
            };

// ---------------------------------------------------------------------------------------------------------------------
            $(document).ready(function() {
                console.log('=================< topics2/tabs.js >=================');
                initFunctions();

                // Move the Assessment Information block when active
                if ( $('.block_assessment_information').length > 0) {
                    // Move the block into it's area in the main region
                    $('#assessment_information_area').append($('.block_assessment_information'));
                    // If the AI block is the only one remove the 'has-blocks' class from the main region
                    if ( $('.block').length === $('.block_assessment_information').length) {
                        $('#region-main').removeClass('has-blocks');
                    }
                }

                // Show the edit menu for section-0
                $("#section-0 .right.side").show();

                // Truncate tab names when option is set
                truncateAllTabnames();

                // Make tabs draggable when in edit mode (the pencil class is present)
                if ($('.inplaceeditable').length > 0) {

                    $('.topictab').parent().draggable({
                        cursor: 'move',
                        stack: '.tabitem', // Make sure the dragged tab is always on top of others
                        revert: true,
                    });
                    $('.topictab').parent().droppable({
                        accept: '.tabitem',
                        hoverClass: 'hovered',
                        drop: handleTabDropEvent,
                    });
                }

                // Check for courseid and tab cookies
                // Only when the page is reloaded for the same course check for a tab cookie and delete it otherwise
                var courseid = $('#courseid').attr('courseid');
                var originCourseid = sessionStorage.getItem('courseid');
                var tabid = null;
                if(originCourseid !== null && originCourseid == courseid) {
                    tabid = sessionStorage.getItem('tabid');
                } else {
                    sessionStorage.removeItem('courseid');
                    sessionStorage.removeItem('tabid');
                }

                // Click all tabs once
                $('#tab0').click();
                $('.tablink:visible').click();

                // If there are visible tabs click them all once to potentially reveal any section names as tab names
                if ($(".topictab:visible").length > 0) {
                    if(tabid !== null && tabid != 'tab0') {
//                        console.log('Found tabid = ' + tabid);

                        // if a 'numSections' cookie is set the changenumsections url has been clicked
                        // while the particular tab was active and we have returned here
                        // if the tabid is other than tab0 move the newly added sections to that tab

                        //get the number of sections before new ones were added from another cookie
                        var numSections = sessionStorage.getItem('numSections');
                        sessionStorage.removeItem('numSections');
                        if(numSections !== null) {
                            // attach all new sections to the given tab
                            var tabnum = tabid.substring(3); // This is the tab number(!) where the section is moved to
                            var i = 0;
                            $('.section.main').each(function(){
                                i = i + 1;
                                if (i > numSections) {
                                    // X console.log('new section id = ' + $(this).attr('id'));
                                    var sectionid = $(this).attr('section-id');
                                    var sectionnum = $(this).attr('id').substring(8);
                                    add2tab(tabnum, sectionid, sectionnum);
                                }
                            });
                            save2tab(tabid);
                        }

                        // click the tab with the found tab ID
                        $('#' + tabid).click();
                    } else {
                        // Click the 1st visible tab by default
                        $('.tablink:visible').first().click();
                    }
                }

                // If section0 is on top restrict section menu - restore otherwise
                if ($("#ontop_area").hasClass('section0_ontop')) {
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
