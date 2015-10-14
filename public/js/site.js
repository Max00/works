!(function($) {
    "use strict";
    $.extend({
        tablesorter: new function() {

            var ts = this;

            ts.version = "2.4.8";

            ts.parsers = [];
            ts.widgets = [];
            ts.defaults = {

                // appearance
                theme            : 'default',  // adds tablesorter-{theme} to the table for styling
                widthFixed       : false,      // adds colgroup to fix widths of columns
                showProcessing   : false,      // show an indeterminate timer icon in the header when the table is sorted or filtered.

                // functionality
                cancelSelection  : true,       // prevent text selection in the header
                dateFormat       : 'mmddyyyy', // other options: "ddmmyyy" or "yyyymmdd"
                sortMultiSortKey : 'shiftKey', // key used to select additional columns
                usNumberFormat   : true,       // false for German "1.234.567,89" or French "1 234 567,89"
                delayInit        : false,      // if false, the parsed table contents will not update until the first sort

                // sort options
                headers          : {},         // set sorter, string, empty, locked order, sortInitialOrder, filter, etc.
                ignoreCase       : true,       // ignore case while sorting
                sortForce        : null,       // column(s) first sorted; always applied
                sortList         : [],         // Initial sort order; applied initially; updated when manually sorted
                sortAppend       : null,       // column(s) sorted last; always applied

                sortInitialOrder : 'asc',      // sort direction on first click
                sortLocaleCompare: false,      // replace equivalent character (accented characters)
                sortReset        : false,      // third click on the header will reset column to default - unsorted
                sortRestart      : false,      // restart sort to "sortInitialOrder" when clicking on previously unsorted columns

                emptyTo          : 'bottom',   // sort empty cell to bottom, top, none, zero
                stringTo         : 'max',      // sort strings in numerical column as max, min, top, bottom, zero
                textExtraction   : 'simple',   // text extraction method/function - function(node, table, cellIndex){}
                textSorter       : null,       // use custom text sorter - function(a,b){ return a.sort(b); } // basic sort

                // widget options
                widgets: [],                   // method to add widgets, e.g. widgets: ['zebra']
                widgetOptions    : {
                    zebra : [ 'even', 'odd' ]    // zebra widget alternating row class names
                },
                initWidgets      : true,       // apply widgets on tablesorter initialization

                // callbacks
                initialized      : null,       // function(table){},
                onRenderHeader   : null,       // function(index){},

                // css class names
                tableClass       : 'tablesorter',
                cssAsc           : 'tablesorter-headerSortUp',
                cssChildRow      : 'tablesorter-childRow', // previously "expand-child"
                cssDesc          : 'tablesorter-headerSortDown',
                cssHeader        : 'tablesorter-header',
                cssHeaderRow     : 'tablesorter-headerRow',
                cssIcon          : 'tablesorter-icon', //  if this class exists, a <i> will be added to the header automatically
                cssInfoBlock     : 'tablesorter-infoOnly', // don't sort tbody with this class name
                cssProcessing    : 'tablesorter-processing', // processing icon applied to header during sort/filter

                // selectors
                selectorHeaders  : '> thead th, > thead td',
                selectorSort     : 'th, td',   // jQuery selector of content within selectorHeaders that is clickable to trigger a sort
                selectorRemove   : '.remove-me',

                // advanced
                debug            : false,

                // Internal variables
                headerList: [],
                empties: {},
                strings: {},
                parsers: []

                // deprecated; but retained for backwards compatibility
                // widgetZebra: { css: ["even", "odd"] }

            };

            /* debuging utils */
            function log(s) {
                if (typeof console !== "undefined" && typeof console.log !== "undefined") {
                    //console.log(s);
                } else {
                    alert(s);
                }
            }

            function benchmark(s, d) {
                log(s + " (" + (new Date().getTime() - d.getTime()) + "ms)");
            }

            ts.benchmark = benchmark;

            function getElementText(table, node, cellIndex) {
                if (!node) { return ""; }
                var c = table.config,
                    t = c.textExtraction, text = "";
                if (t === "simple") {
                    if (c.supportsTextContent) {
                        text = node.textContent; // newer browsers support this
                    } else {
                        text = $(node).text();
                    }
                } else {
                    if (typeof(t) === "function") {
                        text = t(node, table, cellIndex);
                    } else if (typeof(t) === "object" && t.hasOwnProperty(cellIndex)) {
                        text = t[cellIndex](node, table, cellIndex);
                    } else {
                        text = c.supportsTextContent ? node.textContent : $(node).text();
                    }
                }
                return $.trim(text);
            }

            function detectParserForColumn(table, rows, rowIndex, cellIndex) {
                var i, l = ts.parsers.length,
                node = false,
                nodeValue = '',
                keepLooking = true;
                while (nodeValue === '' && keepLooking) {
                    rowIndex++;
                    if (rows[rowIndex]) {
                        node = rows[rowIndex].cells[cellIndex];
                        nodeValue = getElementText(table, node, cellIndex);
                        if (table.config.debug) {
                            log('Checking if value was empty on row ' + rowIndex + ', column: ' + cellIndex + ': ' + nodeValue);
                        }
                    } else {
                        keepLooking = false;
                    }
                }
                for (i = 1; i < l; i++) {
                    if (ts.parsers[i].is(nodeValue, table, node)) {
                        return ts.parsers[i];
                    }
                }
                // 0 is always the generic parser (text)
                return ts.parsers[0];
            }

            function buildParserCache(table) {
                var c = table.config,
                    tb = $(table.tBodies).filter(':not(.' + c.cssInfoBlock + ')'),
                    rows, list, l, i, h, ch, p, parsersDebug = "";
                if ( tb.length === 0) { return; } // In the case of empty tables
                rows = tb[0].rows;
                if (rows[0]) {
                    list = [];
                    l = rows[0].cells.length;
                    for (i = 0; i < l; i++) {
                        // tons of thanks to AnthonyM1229 for working out the following selector (issue #74) to make this work in IE8!
                        // More fixes to this selector to work properly in iOS and jQuery 1.8+ (issue #132 & #174)
                        h = c.$headers.filter(':not([colspan])');
                        h = h.add( c.$headers.filter('[colspan="1"]') ) // ie8 fix
                            .filter('[data-column="' + i + '"]:last');
                        ch = c.headers[i];
                        // get column parser
                        p = ts.getParserById( ts.getData(h, ch, 'sorter') );
                        // empty cells behaviour - keeping emptyToBottom for backwards compatibility
                        c.empties[i] = ts.getData(h, ch, 'empty') || c.emptyTo || (c.emptyToBottom ? 'bottom' : 'top' );
                        // text strings behaviour in numerical sorts
                        c.strings[i] = ts.getData(h, ch, 'string') || c.stringTo || 'max';
                        if (!p) {
                            p = detectParserForColumn(table, rows, -1, i);
                        }
                        if (c.debug) {
                            parsersDebug += "column:" + i + "; parser:" + p.id + "; string:" + c.strings[i] + '; empty: ' + c.empties[i] + "\n";
                        }
                        list.push(p);
                    }
                }
                if (c.debug) {
                    log(parsersDebug);
                }
                return list;
            }

            /* utils */
            function buildCache(table) {
                var b = table.tBodies,
                tc = table.config,
                totalRows,
                totalCells,
                parsers = tc.parsers,
                t, i, j, k, c, cols, cacheTime;
                tc.cache = {};
                if (tc.debug) {
                    cacheTime = new Date();
                }
                // processing icon
                if (tc.showProcessing) {
                    ts.isProcessing(table, true);
                }
                for (k = 0; k < b.length; k++) {
                    tc.cache[k] = { row: [], normalized: [] };
                    // ignore tbodies with class name from css.cssInfoBlock
                    if (!$(b[k]).hasClass(tc.cssInfoBlock)) {
                        totalRows = (b[k] && b[k].rows.length) || 0;
                        totalCells = (b[k].rows[0] && b[k].rows[0].cells.length) || 0;
                        for (i = 0; i < totalRows; ++i) {
                            /** Add the table data to main data array */
                            c = $(b[k].rows[i]);
                            cols = [];
                            // if this is a child row, add it to the last row's children and continue to the next row
                            if (c.hasClass(tc.cssChildRow)) {
                                tc.cache[k].row[tc.cache[k].row.length - 1] = tc.cache[k].row[tc.cache[k].row.length - 1].add(c);
                                // go to the next for loop
                                continue;
                            }
                            tc.cache[k].row.push(c);
                            for (j = 0; j < totalCells; ++j) {
                                t = getElementText(table, c[0].cells[j], j);
                                // allow parsing if the string is empty, previously parsing would change it to zero,
                                // in case the parser needs to extract data from the table cell attributes
                                cols.push( parsers[j].format(t, table, c[0].cells[j], j) );
                            }
                            cols.push(tc.cache[k].normalized.length); // add position for rowCache
                            tc.cache[k].normalized.push(cols);
                        }
                    }
                }
                if (tc.showProcessing) {
                    ts.isProcessing(table); // remove processing icon
                }
                if (tc.debug) {
                    benchmark("Building cache for " + totalRows + " rows", cacheTime);
                }
            }

            // init flag (true) used by pager plugin to prevent widget application
            function appendToTable(table, init) {
                var c = table.config,
                b = table.tBodies,
                rows = [],
                c2 = c.cache,
                r, n, totalRows, checkCell, $bk, $tb,
                i, j, k, l, pos, appendTime;
                if (c.debug) {
                    appendTime = new Date();
                }
                for (k = 0; k < b.length; k++) {
                    $bk = $(b[k]);
                    if (!$bk.hasClass(c.cssInfoBlock)) {
                        // get tbody
                        $tb = ts.processTbody(table, $bk, true);
                        r = c2[k].row;
                        n = c2[k].normalized;
                        totalRows = n.length;
                        checkCell = totalRows ? (n[0].length - 1) : 0;
                        for (i = 0; i < totalRows; i++) {
                            pos = n[i][checkCell];
                            rows.push(r[pos]);
                            // removeRows used by the pager plugin
                            if (!c.appender || !c.removeRows) {
                                l = r[pos].length;
                                for (j = 0; j < l; j++) {
                                    $tb.append(r[pos][j]);
                                }
                            }
                        }
                        // restore tbody
                        ts.processTbody(table, $tb, false);
                    }
                }
                if (c.appender) {
                    c.appender(table, rows);
                }
                if (c.debug) {
                    benchmark("Rebuilt table", appendTime);
                }
                // apply table widgets
                if (!init) { ts.applyWidget(table); }
                // trigger sortend
                $(table).trigger("sortEnd", table);
            }

            // computeTableHeaderCellIndexes from:
            // http://www.javascripttoolbox.com/lib/table/examples.php
            // http://www.javascripttoolbox.com/temp/table_cellindex.html
            function computeThIndexes(t) {
                var matrix = [],
                lookup = {},
                trs = $(t).find('thead:eq(0) tr, tfoot tr'),
                i, j, k, l, c, cells, rowIndex, cellId, rowSpan, colSpan, firstAvailCol, matrixrow;
                for (i = 0; i < trs.length; i++) {
                    cells = trs[i].cells;
                    for (j = 0; j < cells.length; j++) {
                        c = cells[j];
                        rowIndex = c.parentNode.rowIndex;
                        cellId = rowIndex + "-" + c.cellIndex;
                        rowSpan = c.rowSpan || 1;
                        colSpan = c.colSpan || 1;
                        if (typeof(matrix[rowIndex]) === "undefined") {
                            matrix[rowIndex] = [];
                        }
                        // Find first available column in the first row
                        for (k = 0; k < matrix[rowIndex].length + 1; k++) {
                            if (typeof(matrix[rowIndex][k]) === "undefined") {
                                firstAvailCol = k;
                                break;
                            }
                        }
                        lookup[cellId] = firstAvailCol;
                        // add data-column
                        $(c).attr({ 'data-column' : firstAvailCol }); // 'data-row' : rowIndex
                        for (k = rowIndex; k < rowIndex + rowSpan; k++) {
                            if (typeof(matrix[k]) === "undefined") {
                                matrix[k] = [];
                            }
                            matrixrow = matrix[k];
                            for (l = firstAvailCol; l < firstAvailCol + colSpan; l++) {
                                matrixrow[l] = "x";
                            }
                        }
                    }
                }
                return lookup;
            }

            function formatSortingOrder(v) {
                // look for "d" in "desc" order; return true
                return (/^d/i.test(v) || v === 1);
            }

            function buildHeaders(table) {
                var header_index = computeThIndexes(table), ch, $t,
                    t, lock, time, $tableHeaders, c = table.config;
                    c.headerList = [];
                if (c.debug) {
                    time = new Date();
                }
                $tableHeaders = $(table).find(c.selectorHeaders).each(function(index) {
                    $t = $(this);
                    ch = c.headers[index];
                    t = c.cssIcon ? '<i class="' + c.cssIcon + '"></i>' : ''; // add icon if cssIcon option exists
                    this.innerHTML = '<div class="tablesorter-header-inner">' + this.innerHTML + t + '</div>'; // faster than wrapInner
                    if (c.onRenderHeader) { c.onRenderHeader.apply($t, [index]); }
                    this.column = header_index[this.parentNode.rowIndex + "-" + this.cellIndex];
                    this.order = formatSortingOrder( ts.getData($t, ch, 'sortInitialOrder') || c.sortInitialOrder ) ? [1,0,2] : [0,1,2];
                    this.count = -1; // set to -1 because clicking on the header automatically adds one
                    if (ts.getData($t, ch, 'sorter') === 'false') {
                        this.sortDisabled = true;
                        $t.addClass('sorter-false');
                    } else {
                        $t.removeClass('sorter-false');
                    }
                    this.lockedOrder = false;
                    lock = ts.getData($t, ch, 'lockedOrder') || false;
                    if (typeof(lock) !== 'undefined' && lock !== false) {
                        this.order = this.lockedOrder = formatSortingOrder(lock) ? [1,1,1] : [0,0,0];
                    }
                    $t.addClass( (this.sortDisabled ? 'sorter-false ' : ' ') + c.cssHeader );
                    // add cell to headerList
                    c.headerList[index] = this;
                    // add to parent in case there are multiple rows
                    $t.parent().addClass(c.cssHeaderRow);
                });
                if (table.config.debug) {
                    benchmark("Built headers:", time);
                    log($tableHeaders);
                }
                return $tableHeaders;
            }

            function setHeadersCss(table) {
                var f, i, j, l,
                    c = table.config,
                    list = c.sortList,
                    css = [c.cssDesc, c.cssAsc],
                    // find the footer
                    $t = $(table).find('tfoot tr').children().removeClass(css.join(' '));
                // remove all header information
                c.$headers.removeClass(css.join(' '));
                l = list.length;
                for (i = 0; i < l; i++) {
                    // direction = 2 means reset!
                    if (list[i][1] !== 2) {
                        // multicolumn sorting updating - choose the :last in case there are nested columns
                        f = c.$headers.not('.sorter-false').filter('[data-column="' + list[i][0] + '"]' + (l === 1 ? ':last' : '') );
                        if (f.length) {
                            for (j = 0; j < f.length; j++) {
                                if (!f[j].sortDisabled) {
                                    f.eq(j).addClass(css[list[i][1]]);
                                    // add sorted class to footer, if it exists
                                    if ($t.length) {
                                        $t.filter('[data-column="' + list[i][0] + '"]').eq(j).addClass(css[list[i][1]]); 
                                    }
                                }
                            }
                        }
                    }
                }
            }

            function fixColumnWidth(table) {
                if (table.config.widthFixed && $(table).find('colgroup').length === 0) {
                    var colgroup = $('<colgroup>'),
                        overallWidth = $(table).width();
                    $("tr:first td", table.tBodies[0]).each(function() {
                        colgroup.append($('<col>').css('width', parseInt(($(this).width()/overallWidth)*1000, 10)/10 + '%'));
                    });
                    $(table).prepend(colgroup);
                }
            }

            function updateHeaderSortCount(table, list) {
                var s, o, c = table.config,
                    l = c.headerList.length,
                    sl = list || c.sortList;
                c.sortList = [];
                $.each(sl, function(i,v){
                    // ensure all sortList values are numeric - fixes #127
                    s = [ parseInt(v[0], 10), parseInt(v[1], 10) ];
                    // make sure header exists
                    o = c.headerList[s[0]];
                    if (o) { // prevents error if sorton array is wrong
                        c.sortList.push(s);
                        o.count = s[1] % (c.sortReset ? 3 : 2);
                    }
                });
            }

            function getCachedSortType(parsers, i) {
                return (parsers && parsers[i]) ? parsers[i].type || '' : '';
            }

            // sort multiple columns
            function multisort(table) {
                var dynamicExp, sortWrapper, col, mx = 0, dir = 0, tc = table.config,
                sortList = tc.sortList, l = sortList.length, bl = table.tBodies.length,
                sortTime, i, j, k, c, cache, lc, s, e, order, orgOrderCol;
                if (tc.debug) { sortTime = new Date(); }
                for (k = 0; k < bl; k++) {
                    dynamicExp = "sortWrapper = function(a,b) {";
                    cache = tc.cache[k];
                    lc = cache.normalized.length;
                    for (i = 0; i < l; i++) {
                        c = sortList[i][0];
                        order = sortList[i][1];
                        // fallback to natural sort since it is more robust
                        s = /n/i.test(getCachedSortType(tc.parsers, c)) ? "Numeric" : "Text";
                        s += order === 0 ? "" : "Desc";
                        e = "e" + i;
                        // get max column value (ignore sign)
                        if (/Numeric/.test(s) && tc.strings[c]) {
                            for (j = 0; j < lc; j++) {
                                col = Math.abs(parseFloat(cache.normalized[j][c]));
                                mx = Math.max( mx, isNaN(col) ? 0 : col );
                            }
                            // sort strings in numerical columns
                            if (typeof(tc.string[tc.strings[c]]) === 'boolean') {
                                dir = (order === 0 ? 1 : -1) * (tc.string[tc.strings[c]] ? -1 : 1);
                            } else {
                                dir = (tc.strings[c]) ? tc.string[tc.strings[c]] || 0 : 0;
                            }
                        }
                        dynamicExp += "var " + e + " = $.tablesorter.sort" + s + "(table,a[" + c + "],b[" + c + "]," + c + "," + mx +  "," + dir + "); ";
                        dynamicExp += "if (" + e + ") { return " + e + "; } ";
                        dynamicExp += "else { ";
                    }
                    // if value is the same keep orignal order
                    orgOrderCol = (cache.normalized && cache.normalized[0]) ? cache.normalized[0].length - 1 : 0;
                    dynamicExp += "return a[" + orgOrderCol + "]-b[" + orgOrderCol + "];";
                    for (i=0; i < l; i++) {
                        dynamicExp += "}; ";
                    }
                    dynamicExp += "return 0; ";
                    dynamicExp += "}; ";
                    cache.normalized.sort(eval(dynamicExp)); // sort using eval expression
                }
                if (tc.debug) { benchmark("Sorting on " + sortList.toString() + " and dir " + order + " time", sortTime); }
            }

            function resortComplete($table, callback){
                $table.trigger('updateComplete');
                if (typeof callback === "function") {
                    callback($table[0]);
                }
            }

            function checkResort($table, flag, callback) {
                if (flag !== false) {
                    $table.trigger("sorton", [$table[0].config.sortList, function(){
                        resortComplete($table, callback);
                    }]);
                } else {
                    resortComplete($table, callback);
                }
            }

            /* public methods */
            ts.construct = function(settings) {
                return this.each(function() {
                    // if no thead or tbody, or tablesorter is already present, quit
                    if (!this.tHead || this.tBodies.length === 0 || this.hasInitialized === true) { return; }
                    // declare
                    var $cell, $this = $(this),
                        c, i, j, k = '', a, s, o, downTime,
                        m = $.metadata;
                    // initialization flag
                    this.hasInitialized = false;
                    // new blank config object
                    this.config = {};
                    // merge and extend
                    c = $.extend(true, this.config, ts.defaults, settings);
                    // save the settings where they read
                    $.data(this, "tablesorter", c);
                    if (c.debug) { $.data( this, 'startoveralltimer', new Date()); }
                    // constants
                    c.supportsTextContent = $('<span>x</span>')[0].textContent === 'x';
                    c.supportsDataObject = parseFloat($.fn.jquery) >= 1.4;
                    // digit sort text location; keeping max+/- for backwards compatibility
                    c.string = { 'max': 1, 'min': -1, 'max+': 1, 'max-': -1, 'zero': 0, 'none': 0, 'null': 0, 'top': true, 'bottom': false };
                    // add table theme class only if there isn't already one there
                    if (!/tablesorter\-/.test($this.attr('class'))) {
                        k = (c.theme !== '' ? ' tablesorter-' + c.theme : '');
                    }
                    $this.addClass(c.tableClass + k);
                    // build headers
                    c.$headers = buildHeaders(this);
                    // try to auto detect column type, and store in tables config
                    c.parsers = buildParserCache(this);
                    // build the cache for the tbody cells
                    // delayInit will delay building the cache until the user starts a sort
                    if (!c.delayInit) { buildCache(this); }
                    // apply event handling to headers
                    // this is to big, perhaps break it out?
                    c.$headers
                    // http://stackoverflow.com/questions/5312849/jquery-find-self
                    .find('*').andSelf().filter(c.selectorSort)
                    .unbind('mousedown.tablesorter mouseup.tablesorter')
                    .bind('mousedown.tablesorter mouseup.tablesorter', function(e, external) {
                        // jQuery v1.2.6 doesn't have closest()
                        var $cell = this.tagName.match('TH|TD') ? $(this) : $(this).parents('th, td').filter(':last'), cell = $cell[0];
                        // only recognize left clicks
                        if ((e.which || e.button) !== 1) { return false; }
                        // set timer on mousedown
                        if (e.type === 'mousedown') {
                            downTime = new Date().getTime();
                            return e.target.tagName === "INPUT" ? '' : !c.cancelSelection;
                        }
                        // ignore long clicks (prevents resizable widget from initializing a sort)
                        if (external !== true && (new Date().getTime() - downTime > 250)) { return false; }
                        if (c.delayInit && !c.cache) { buildCache($this[0]); }
                        if (!cell.sortDisabled) {
                            // Only call sortStart if sorting is enabled
                            $this.trigger("sortStart", $this[0]);
                            // store exp, for speed
                            // $cell = $(this);
                            k = !e[c.sortMultiSortKey];
                            // get current column sort order
                            cell.count = (cell.count + 1) % (c.sortReset ? 3 : 2);
                            // reset all sorts on non-current column - issue #30
                            if (c.sortRestart) {
                                i = cell;
                                c.$headers.each(function() {
                                    // only reset counts on columns that weren't just clicked on and if not included in a multisort
                                    if (this !== i && (k || !$(this).is('.' + c.cssDesc + ',.' + c.cssAsc))) {
                                        this.count = -1;
                                    }
                                });
                            }
                            // get current column index
                            i = cell.column;
                            // user only wants to sort on one column
                            if (k) {
                                // flush the sort list
                                c.sortList = [];
                                if (c.sortForce !== null) {
                                    a = c.sortForce;
                                    for (j = 0; j < a.length; j++) {
                                        if (a[j][0] !== i) {
                                            c.sortList.push(a[j]);
                                        }
                                    }
                                }
                                // add column to sort list
                                o = cell.order[cell.count];
                                if (o < 2) {
                                    c.sortList.push([i, o]);
                                    // add other columns if header spans across multiple
                                    if (cell.colSpan > 1) {
                                        for (j = 1; j < cell.colSpan; j++) {
                                            c.sortList.push([i + j, o]);
                                        }
                                    }
                                }
                                // multi column sorting
                            } else {
                                // get rid of the sortAppend before adding more - fixes issue #115
                                if (c.sortAppend && c.sortList.length > 1) {
                                    if (ts.isValueInArray(c.sortAppend[0][0], c.sortList)) {
                                        c.sortList.pop();
                                    }
                                }
                                // the user has clicked on an already sorted column
                                if (ts.isValueInArray(i, c.sortList)) {
                                    // reverse the sorting direction for all tables
                                    for (j = 0; j < c.sortList.length; j++) {
                                        s = c.sortList[j];
                                        o = c.headerList[s[0]];
                                        if (s[0] === i) {
                                            s[1] = o.order[o.count];
                                            if (s[1] === 2) {
                                                c.sortList.splice(j,1);
                                                o.count = -1;
                                            }
                                        }
                                    }
                                } else {
                                    // add column to sort list array
                                    o = cell.order[cell.count];
                                    if (o < 2) {
                                        c.sortList.push([i, o]);
                                        // add other columns if header spans across multiple
                                        if (cell.colSpan > 1) {
                                            for (j = 1; j < cell.colSpan; j++) {
                                                c.sortList.push([i + j, o]);
                                            }
                                        }
                                    }
                                }
                            }
                            if (c.sortAppend !== null) {
                                a = c.sortAppend;
                                for (j = 0; j < a.length; j++) {
                                    if (a[j][0] !== i) {
                                        c.sortList.push(a[j]);
                                    }
                                }
                            }
                            // sortBegin event triggered immediately before the sort
                            $this.trigger("sortBegin", $this[0]);
                            // setTimeout needed so the processing icon shows up
                            setTimeout(function(){
                                // set css for headers
                                setHeadersCss($this[0]);
                                multisort($this[0]);
                                appendToTable($this[0]);
                            }, 1);
                        }
                    });
                    if (c.cancelSelection) {
                        // cancel selection
                        c.$headers.each(function() {
                            this.onselectstart = function() {
                                return false;
                            };
                        });
                    }
                    // apply easy methods that trigger binded events
                    $this
                    .unbind('sortReset update updateCell addRows sorton appendCache applyWidgetId applyWidgets refreshWidgets destroy mouseup mouseleave')
                    .bind("sortReset", function(){
                        c.sortList = [];
                        setHeadersCss(this);
                        multisort(this);
                        appendToTable(this);
                    })
                    .bind("update", function(e, resort, callback) {
                        // remove rows/elements before update
                        $(c.selectorRemove, this).remove();
                        // rebuild parsers
                        c.parsers = buildParserCache(this);
                        // rebuild the cache map
                        buildCache(this);
                        checkResort($this, resort, callback);
                    })
                    .bind("updateCell", function(e, cell, resort, callback) {
                        // get position from the dom
                        var l, row, icell,
                        t = this, $tb = $(this).find('tbody'),
                        // update cache - format: function(s, table, cell, cellIndex)
                        // no closest in jQuery v1.2.6 - tbdy = $tb.index( $(cell).closest('tbody') ),$row = $(cell).closest('tr');
                        tbdy = $tb.index( $(cell).parents('tbody').filter(':last') ),
                        $row = $(cell).parents('tr').filter(':last');
                        // tbody may not exist if update is initialized while tbody is removed for processing
                        if ($tb.length && tbdy >= 0) {
                            row = $tb.eq(tbdy).find('tr').index( $row );
                            icell = cell.cellIndex;
                            l = t.config.cache[tbdy].normalized[row].length - 1;
                            t.config.cache[tbdy].row[t.config.cache[tbdy].normalized[row][l]] = $row;
                            t.config.cache[tbdy].normalized[row][icell] = c.parsers[icell].format( getElementText(t, cell, icell), t, cell, icell );
                            checkResort($this, resort, callback);
                        }
                    })
                    .bind("addRows", function(e, $row, resort, callback) {
                        var i, rows = $row.filter('tr').length,
                        dat = [], l = $row[0].cells.length, t = this,
                        tbdy = $(this).find('tbody').index( $row.closest('tbody') );
                        // add each row
                        for (i = 0; i < rows; i++) {
                            // add each cell
                            for (j = 0; j < l; j++) {
                                dat[j] = c.parsers[j].format( getElementText(t, $row[i].cells[j], j), t, $row[i].cells[j], j );
                            }
                            // add the row index to the end
                            dat.push(c.cache[tbdy].row.length);
                            // update cache
                            c.cache[tbdy].row.push([$row[i]]);
                            c.cache[tbdy].normalized.push(dat);
                            dat = [];
                        }
                        // resort using current settings
                        checkResort($this, resort, callback);
                    })
                    .bind("sorton", function(e, list, callback, init) {
                        $(this).trigger("sortStart", this);
                        // update header count index
                        updateHeaderSortCount(this, list);
                        // set css for headers
                        setHeadersCss(this);
                        // sort the table and append it to the dom
                        multisort(this);
                        appendToTable(this, init);
                        if (typeof callback === "function") {
                            callback(this);
                        }
                    })
                    .bind("appendCache", function(e, callback, init) {
                        appendToTable(this, init);
                        if (typeof callback === "function") {
                            callback(this);
                        }
                    })
                    .bind("applyWidgetId", function(e, id) {
                        ts.getWidgetById(id).format(this, c, c.widgetOptions);
                    })
                    .bind("applyWidgets", function(e, init) {
                        // apply widgets
                        ts.applyWidget(this, init);
                    })
                    .bind("refreshWidgets", function(e, all, dontapply){
                        ts.refreshWidgets(this, all, dontapply);
                    })
                    .bind("destroy", function(e, c, cb){
                        ts.destroy(this, c, cb);
                    });

                    // get sort list from jQuery data or metadata
                    // in jQuery < 1.4, an error occurs when calling $this.data()
                    if (c.supportsDataObject && typeof $this.data().sortlist !== 'undefined') {
                        c.sortList = $this.data().sortlist;
                    } else if (m && ($this.metadata() && $this.metadata().sortlist)) {
                        c.sortList = $this.metadata().sortlist;
                    }
                    // apply widget init code
                    ts.applyWidget(this, true);
                    // if user has supplied a sort list to constructor
                    if (c.sortList.length > 0) {
                        $this.trigger("sorton", [c.sortList, {}, !c.initWidgets]);
                    } else if (c.initWidgets) {
                        // apply widget format
                        ts.applyWidget(this);
                    }

                    // fixate columns if the users supplies the fixedWidth option
                    // do this after theme has been applied
                    fixColumnWidth(this);

                    // show processesing icon
                    if (c.showProcessing) {
                        $this
                        .unbind('sortBegin sortEnd')
                        .bind('sortBegin sortEnd', function(e) {
                            ts.isProcessing($this[0], e.type === 'sortBegin');
                        });
                    }

                    // initialized
                    this.hasInitialized = true;
                    if (c.debug) {
                        ts.benchmark("Overall initialization time", $.data( this, 'startoveralltimer'));
                    }
                    $this.trigger('tablesorter-initialized', this);
                    if (typeof c.initialized === 'function') { c.initialized(this); }
                });
            };

            // *** Process table ***
            // add processing indicator
            ts.isProcessing = function(table, toggle, $ths) {
                var c = table.config,
                    // default to all headers
                    $h = $ths || $(table).find('.' + c.cssHeader);
                if (toggle) {
                    if (c.sortList.length > 0) {
                        // get headers from the sortList
                        $h = $h.filter(function(){
                            // get data-column from attr to keep  compatibility with jQuery 1.2.6
                            return this.sortDisabled ? false : ts.isValueInArray( parseFloat($(this).attr('data-column')), c.sortList);
                        });
                    }
                    $h.addClass(c.cssProcessing);
                } else {
                    $h.removeClass(c.cssProcessing);
                }
            };

            // detach tbody but save the position
            // don't use tbody because there are portions that look for a tbody index (updateCell)
            ts.processTbody = function(table, $tb, getIt){
                var t, holdr;
                if (getIt) {
                    $tb.before('<span class="tablesorter-savemyplace"/>');
                    holdr = ($.fn.detach) ? $tb.detach() : $tb.remove();
                    return holdr;
                }
                holdr = $(table).find('span.tablesorter-savemyplace');
                $tb.insertAfter( holdr );
                holdr.remove();
            };

            ts.clearTableBody = function(table) {
                $(table.tBodies).filter(':not(.' + table.config.cssInfoBlock + ')').empty();
            };

            ts.destroy = function(table, removeClasses, callback){
                var $t = $(table), c = table.config,
                $h = $t.find('thead:first');
                // clear flag in case the plugin is initialized again
                table.hasInitialized = false;
                // remove widget added rows
                $h.find('tr:not(.' + c.cssHeaderRow + ')').remove();
                // remove resizer widget stuff
                $h.find('.tablesorter-resizer').remove();
                // remove all widgets
                ts.refreshWidgets(table, true, true);
                // disable tablesorter
                $t
                    .removeData('tablesorter')
                    .unbind('sortReset update updateCell addRows sorton appendCache applyWidgetId applyWidgets refreshWidgets destroy mouseup mouseleave')
                    .find('.' + c.cssHeader)
                    .unbind('click mousedown mousemove mouseup')
                    .removeClass(c.cssHeader + ' ' + c.cssAsc + ' ' + c.cssDesc)
                    .find('.tablesorter-header-inner').each(function(){
                        if (c.cssIcon !== '') { $(this).find('.' + c.cssIcon).remove(); }
                        $(this).replaceWith( $(this).contents() );
                    });
                if (removeClasses !== false) {
                    $t.removeClass(c.tableClass);
                }
                if (typeof callback === 'function') {
                    callback(table);
                }
            };

            // *** sort functions ***
            // regex used in natural sort
            ts.regex = [
                /(^-?[0-9]+(\.?[0-9]*)[df]?e?[0-9]?$|^0x[0-9a-f]+$|[0-9]+)/gi, // chunk/tokenize numbers & letters
                /(^([\w ]+,?[\w ]+)?[\w ]+,?[\w ]+\d+:\d+(:\d+)?[\w ]?|^\d{1,4}[\/\-]\d{1,4}[\/\-]\d{1,4}|^\w+, \w+ \d+, \d{4})/, //date
                /^0x[0-9a-f]+$/i // hex
            ];

            // Natural sort - https://github.com/overset/javascript-natural-sort
            ts.sortText = function(table, a, b, col) {
                if (a === b) { return 0; }
                var c = table.config, e = c.string[ (c.empties[col] || c.emptyTo ) ],
                    r = ts.regex, xN, xD, yN, yD, xF, yF, i, mx;
                if (a === '' && e !== 0) { return (typeof(e) === 'boolean') ? (e ? -1 : 1) : -e || -1; }
                if (b === '' && e !== 0) { return (typeof(e) === 'boolean') ? (e ? 1 : -1) : e || 1; }
                if (typeof c.textSorter === 'function') { return c.textSorter(a, b, table, col); }
                // chunk/tokenize
                xN = a.replace(r[0], '\\0$1\\0').replace(/\\0$/, '').replace(/^\\0/, '').split('\\0');
                yN = b.replace(r[0], '\\0$1\\0').replace(/\\0$/, '').replace(/^\\0/, '').split('\\0');
                // numeric, hex or date detection
                xD = parseInt(a.match(r[2]),16) || (xN.length !== 1 && a.match(r[1]) && Date.parse(a));
                yD = parseInt(b.match(r[2]),16) || (xD && b.match(r[1]) && Date.parse(b)) || null;
                // first try and sort Hex codes or Dates
                if (yD) {
                    if ( xD < yD ) { return -1; }
                    if ( xD > yD ) { return 1; }
                }
                mx = Math.max(xN.length, yN.length);
                // natural sorting through split numeric strings and default strings
                for (i = 0; i < mx; i++) {
                    // find floats not starting with '0', string or 0 if not defined
                    xF = isNaN(xN[i]) ? xN[i] || 0 : parseFloat(xN[i]) || 0;
                    yF = isNaN(yN[i]) ? yN[i] || 0 : parseFloat(yN[i]) || 0;
                    // handle numeric vs string comparison - number < string - (Kyle Adams)
                    if (isNaN(xF) !== isNaN(yF)) { return (isNaN(xF)) ? 1 : -1; }
                    // rely on string comparison if different types - i.e. '02' < 2 != '02' < '2'
                    if (typeof xF !== typeof yF) {
                        xF += '';
                        yF += '';
                    }
                    if (xF < yF) { return -1; }
                    if (xF > yF) { return 1; }
                }
                return 0;
            };

            ts.sortTextDesc = function(table, a, b, col) {
                if (a === b) { return 0; }
                var c = table.config, e = c.string[ (c.empties[col] || c.emptyTo ) ];
                if (a === '' && e !== 0) { return (typeof(e) === 'boolean') ? (e ? -1 : 1) : e || 1; }
                if (b === '' && e !== 0) { return (typeof(e) === 'boolean') ? (e ? 1 : -1) : -e || -1; }
                if (typeof c.textSorter === 'function') { return c.textSorter(b, a, table, col); }
                return ts.sortText(table, b, a);
            };

            // return text string value by adding up ascii value
            // so the text is somewhat sorted when using a digital sort
            // this is NOT an alphanumeric sort
            ts.getTextValue = function(a, mx, d) {
                if (mx) {
                    // make sure the text value is greater than the max numerical value (mx)
                    var i, l = a.length, n = mx + d;
                    for (i = 0; i < l; i++) {
                        n += a.charCodeAt(i);
                    }
                    return d * n;
                }
                return 0;
            };

            ts.sortNumeric = function(table, a, b, col, mx, d) {
                if (a === b) { return 0; }
                var c = table.config, e = c.string[ (c.empties[col] || c.emptyTo ) ];
                if (a === '' && e !== 0) { return (typeof(e) === 'boolean') ? (e ? -1 : 1) : -e || -1; }
                if (b === '' && e !== 0) { return (typeof(e) === 'boolean') ? (e ? 1 : -1) : e || 1; }
                if (isNaN(a)) { a = ts.getTextValue(a, mx, d); }
                if (isNaN(b)) { b = ts.getTextValue(b, mx, d); }
                return a - b;
            };

            ts.sortNumericDesc = function(table, a, b, col, mx, d) {
                if (a === b) { return 0; }
                var c = table.config, e = c.string[ (c.empties[col] || c.emptyTo ) ];
                if (a === '' && e !== 0) { return (typeof(e) === 'boolean') ? (e ? -1 : 1) : e || 1; }
                if (b === '' && e !== 0) { return (typeof(e) === 'boolean') ? (e ? 1 : -1) : -e || -1; }
                if (isNaN(a)) { a = ts.getTextValue(a, mx, d); }
                if (isNaN(b)) { b = ts.getTextValue(b, mx, d); }
                return b - a;
            };

            // used when replacing accented characters during sorting
            ts.characterEquivalents = {
                "a" : "\u00e1\u00e0\u00e2\u00e3\u00e4", // áàâãä
                "A" : "\u00c1\u00c0\u00c2\u00c3\u00c4", // ÁÀÂÃÄ
                "c" : "\u00e7", // ç
                "C" : "\u00c7", // Ç
                "e" : "\u00e9\u00e8\u00ea\u00eb", // éèêë
                "E" : "\u00c9\u00c8\u00ca\u00cb", // ÉÈÊË
                "i" : "\u00ed\u00ec\u0130\u00ee\u00ef", // íìİîï
                "I" : "\u00cd\u00cc\u0130\u00ce\u00cf", // ÍÌİÎÏ
                "o" : "\u00f3\u00f2\u00f4\u00f5\u00f6", // óòôõö
                "O" : "\u00d3\u00d2\u00d4\u00d5\u00d6", // ÓÒÔÕÖ
                "S" : "\u00df", // ß
                "u" : "\u00fa\u00f9\u00fb\u00fc", // úùûü
                "U" : "\u00da\u00d9\u00db\u00dc" // ÚÙÛÜ
            };
            ts.replaceAccents = function(s) {
                var a, acc = '[', eq = ts.characterEquivalents;
                if (!ts.characterRegex) {
                    ts.characterRegexArray = {};
                    for (a in eq) {
                        if (typeof a === 'string') {
                            acc += eq[a];
                            ts.characterRegexArray[a] = new RegExp('[' + eq[a] + ']', 'g');
                        }
                    }
                    ts.characterRegex = new RegExp(acc + ']');
                }
                if (ts.characterRegex.test(s)) {
                    for (a in eq) {
                        if (typeof a === 'string') {
                            s = s.replace( ts.characterRegexArray[a], a );
                        }
                    }
                }
                return s;
            };

            // *** utilities ***
            ts.isValueInArray = function(v, a) {
                var i, l = a.length;
                for (i = 0; i < l; i++) {
                    if (a[i][0] === v) {
                        return true;
                    }
                }
                return false;
            };

            ts.addParser = function(parser) {
                var i, l = ts.parsers.length, a = true;
                for (i = 0; i < l; i++) {
                    if (ts.parsers[i].id.toLowerCase() === parser.id.toLowerCase()) {
                        a = false;
                    }
                }
                if (a) {
                    ts.parsers.push(parser);
                }
            };

            ts.getParserById = function(name) {
                var i, l = ts.parsers.length;
                for (i = 0; i < l; i++) {
                    if (ts.parsers[i].id.toLowerCase() === (name.toString()).toLowerCase()) {
                        return ts.parsers[i];
                    }
                }
                return false;
            };

            ts.addWidget = function(widget) {
                ts.widgets.push(widget);
            };

            ts.getWidgetById = function(name) {
                var i, w, l = ts.widgets.length;
                for (i = 0; i < l; i++) {
                    w = ts.widgets[i];
                    if (w && w.hasOwnProperty('id') && w.id.toLowerCase() === name.toLowerCase()) {
                        return w;
                    }
                }
            };

            ts.applyWidget = function(table, init) {
                var c = table.config,
                    wo = c.widgetOptions,
                    ws = c.widgets.sort().reverse(), // ensure that widgets are always applied in a certain order
                    time, i, w, l = ws.length;
                // make zebra last
                i = $.inArray('zebra', c.widgets);
                if (i >= 0) {
                    c.widgets.splice(i,1);
                    c.widgets.push('zebra');
                }
                if (c.debug) {
                    time = new Date();
                }
                // add selected widgets
                for (i = 0; i < l; i++) {
                    w = ts.getWidgetById(ws[i]);
                    if ( w ) {
                        if (init === true && w.hasOwnProperty('init')) {
                            w.init(table, w, c, wo);
                        } else if (!init && w.hasOwnProperty('format')) {
                            w.format(table, c, wo);
                        }
                    }
                }
                if (c.debug) {
                    benchmark("Completed " + (init === true ? "initializing" : "applying") + " widgets", time);
                }
            };

            ts.refreshWidgets = function(table, doAll, dontapply) {
                var i, c = table.config,
                    cw = c.widgets,
                    w = ts.widgets, l = w.length;
                // remove previous widgets
                for (i = 0; i < l; i++){
                    if ( w[i] && w[i].id && (doAll || $.inArray( w[i].id, cw ) < 0) ) {
                        if (c.debug) { log( 'removing ' + w[i].id  ); }
                        if (w[i].hasOwnProperty('remove')) { w[i].remove(table, c, c.widgetOptions); }
                    }
                }
                if (dontapply !== true) {
                    ts.applyWidget(table, doAll);
                }
            };

            // get sorter, string, empty, etc options for each column from
            // jQuery data, metadata, header option or header class name ("sorter-false")
            // priority = jQuery data > meta > headers option > header class name
            ts.getData = function(h, ch, key) {
                var val = '', $h = $(h), m, cl;
                if (!$h.length) { return ''; }
                m = $.metadata ? $h.metadata() : false;
                cl = ' ' + ($h.attr('class') || '');
                if (typeof $h.data(key) !== 'undefined' || typeof $h.data(key.toLowerCase()) !== 'undefined'){
                    // "data-lockedOrder" is assigned to "lockedorder"; but "data-locked-order" is assigned to "lockedOrder"
                    // "data-sort-initial-order" is assigned to "sortInitialOrder"
                    val += $h.data(key) || $h.data(key.toLowerCase());
                } else if (m && typeof m[key] !== 'undefined') {
                    val += m[key];
                } else if (ch && typeof ch[key] !== 'undefined') {
                    val += ch[key];
                } else if (cl !== ' ' && cl.match(' ' + key + '-')) {
                    // include sorter class name "sorter-text", etc
                    val = cl.match( new RegExp(' ' + key + '-(\\w+)') )[1] || '';
                }
                return $.trim(val);
            };

            ts.formatFloat = function(s, table) {
                if (typeof(s) !== 'string' || s === '') { return s; }
                if (table.config.usNumberFormat !== false) {
                    // US Format - 1,234,567.89 -> 1234567.89
                    s = s.replace(/,/g,'');
                } else {
                    // German Format = 1.234.567,89 -> 1234567.89
                    // French Format = 1 234 567,89 -> 1234567.89
                    s = s.replace(/[\s|\.]/g,'').replace(/,/g,'.');
                }
                if(/^\s*\([.\d]+\)/.test(s)) {
                    // make (#) into a negative number -> (10) = -10
                    s = s.replace(/^\s*\(/,'-').replace(/\)/,'');
                }
                var i = parseFloat(s);
                // return the text instead of zero
                return isNaN(i) ? $.trim(s) : i;
            };

            ts.isDigit = function(s) {
                // replace all unwanted chars and match
                return isNaN(s) ? (/^[\-+(]?\d+[)]?$/).test(s.toString().replace(/[,.'\s]/g, '')) : true;
            };

        }()
    });

    // make shortcut
    var ts = $.tablesorter;

    // extend plugin scope
    $.fn.extend({
        tablesorter: ts.construct
    });

    // add default parsers
    ts.addParser({
        id: "text",
        is: function(s, table, node) {
            return true;
        },
        format: function(s, table, cell, cellIndex) {
            var c = table.config;
            s = $.trim( c.ignoreCase ? s.toLocaleLowerCase() : s );
            return c.sortLocaleCompare ? ts.replaceAccents(s) : s;
        },
        type: "text"
    });

    ts.addParser({
        id: "currency",
        is: function(s) {
            return (/^\(?[\u00a3$\u20ac\u00a4\u00a5\u00a2?.]\d+/).test(s); // £$€¤¥¢
        },
        format: function(s, table) {
            return ts.formatFloat(s.replace(/[^\w,. \-()]/g, ""), table);
        },
        type: "numeric"
    });

    ts.addParser({
        id: "ipAddress",
        is: function(s) {
            return (/^\d{1,3}[\.]\d{1,3}[\.]\d{1,3}[\.]\d{1,3}$/).test(s);
        },
        format: function(s, table) {
            var i, a = s.split("."),
            r = "",
            l = a.length;
            for (i = 0; i < l; i++) {
                r += ("00" + a[i]).slice(-3);
            }
            return ts.formatFloat(r, table);
        },
        type: "numeric"
    });

    ts.addParser({
        id: "url",
        is: function(s) {
            return (/^(https?|ftp|file):\/\//).test(s);
        },
        format: function(s) {
            return $.trim(s.replace(/(https?|ftp|file):\/\//, ''));
        },
        type: "text"
    });

    ts.addParser({
        id: "isoDate",
        is: function(s) {
            return (/^\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2}$/).test(s);
        },
        format: function(s, table) {
            return ts.formatFloat((s !== "") ? (new Date(s.replace(/-/g, "/")).getTime() || "") : "", table);
        },
        type: "numeric"
    });

    ts.addParser({
        id: "percent",
        is: function(s) {
            return (/\d%\)?$/).test(s);
        },
        format: function(s, table) {
            return ts.formatFloat(s.replace(/%/g, ""), table);
        },
        type: "numeric"
    });

    ts.addParser({
        id: "usLongDate",
        is: function(s) {
            return (/^[A-Z]{3,10}\.?\s+\d{1,2},?\s+(\d{4}|'?\d{2})\s+(([0-2]?\d:[0-5]\d)|([0-1]?\d:[0-5]\d\s?([AP]M)))$/i).test(s);
        },
        format: function(s, table) {
            return ts.formatFloat( (new Date(s.replace(/(\S)([AP]M)$/i, "$1 $2")).getTime() || ''), table);
        },
        type: "numeric"
    });

    ts.addParser({
        id: "shortDate", // "mmddyyyy", "ddmmyyyy" or "yyyymmdd"
        is: function(s) {
            // testing for ####-##-#### - so it's not perfect
            return (/^(\d{2}|\d{4})[\/\-\,\.\s+]\d{2}[\/\-\.\,\s+](\d{2}|\d{4})$/).test(s);
        },
        format: function(s, table, cell, cellIndex) {
            var c = table.config, ci = c.headerList[cellIndex],
            format = ci.shortDateFormat;
            if (typeof format === 'undefined') {
                // cache header formatting so it doesn't getData for every cell in the column
                format = ci.shortDateFormat = ts.getData( ci, c.headers[cellIndex], 'dateFormat') || c.dateFormat;
            }
            s = s.replace(/\s+/g," ").replace(/[\-|\.|\,]/g, "/");
            if (format === "mmddyyyy") {
                s = s.replace(/(\d{1,2})[\/\s](\d{1,2})[\/\s](\d{4})/, "$3/$1/$2");
            } else if (format === "ddmmyyyy") {
                s = s.replace(/(\d{1,2})[\/\s](\d{1,2})[\/\s](\d{4})/, "$3/$2/$1");
            } else if (format === "yyyymmdd") {
                s = s.replace(/(\d{4})[\/\s](\d{1,2})[\/\s](\d{1,2})/, "$1/$2/$3");
            }
            return ts.formatFloat( (new Date(s).getTime() || ''), table);
        },
        type: "numeric"
    });

    ts.addParser({
        id: "time",
        is: function(s) {
            return (/^(([0-2]?\d:[0-5]\d)|([0-1]?\d:[0-5]\d\s?([AP]M)))$/i).test(s);
        },
        format: function(s, table) {
            return ts.formatFloat( (new Date("2000/01/01 " + s.replace(/(\S)([AP]M)$/i, "$1 $2")).getTime() || ""), table);
        },
        type: "numeric"
    });

    ts.addParser({
        id: "digit",
        is: function(s) {
            return ts.isDigit(s);
        },
        format: function(s, table) {
            return ts.formatFloat(s.replace(/[^\w,. \-()]/g, ""), table);
        },
        type: "numeric"
    });

    ts.addParser({
        id: "metadata",
        is: function(s) {
            return false;
        },
        format: function(s, table, cell) {
            var c = table.config,
            p = (!c.parserMetadataName) ? 'sortValue' : c.parserMetadataName;
            return $(cell).metadata()[p];
        },
        type: "numeric"
    });

    // add default widgets
    ts.addWidget({
        id: "zebra",
        format: function(table, c, wo) {
            var $tb, $tv, $tr, row, even, time, k, l,
            child = new RegExp(c.cssChildRow, 'i'),
            b = $(table).children('tbody:not(.' + c.cssInfoBlock + ')');
            if (c.debug) {
                time = new Date();
            }
            for (k = 0; k < b.length; k++ ) {
                // loop through the visible rows
                $tb = $(b[k]);
                l = $tb.children('tr').length;
                if (l > 1) {
                    row = 0;
                    $tv = $tb.children('tr:visible');
                    // revered back to using jQuery each - strangely it's the fastest method
                    $tv.each(function(){
                        $tr = $(this);
                        // style children rows the same way the parent row was styled
                        if (!child.test(this.className)) { row++; }
                        even = (row % 2 === 0);
                        $tr.removeClass(wo.zebra[even ? 1 : 0]).addClass(wo.zebra[even ? 0 : 1]);
                    });
                }
            }
            if (c.debug) {
                ts.benchmark("Applying Zebra widget", time);
            }
        },
        remove: function(table, c, wo){
            var k, $tb,
                b = $(table).children('tbody:not(.' + c.cssInfoBlock + ')'),
                rmv = (c.widgetOptions.zebra || [ "even", "odd" ]).join(' ');
            for (k = 0; k < b.length; k++ ){
                $tb = $.tablesorter.processTbody(table, $(b[k]), true); // remove tbody
                $tb.children().removeClass(rmv);
                $.tablesorter.processTbody(table, $tb, false); // restore tbody
            }
        }
    });

})(jQuery);

String.prototype.stripAccents = function(){
    var accent = [
        /[\300-\306]/g, /[\340-\346]/g, // A, a
        /[\310-\313]/g, /[\350-\353]/g, // E, e
        /[\314-\317]/g, /[\354-\357]/g, // I, i
        /[\322-\330]/g, /[\362-\370]/g, // O, o
        /[\331-\334]/g, /[\371-\374]/g, // U, u
        /[\321]/g, /[\361]/g, // N, n
        /[\307]/g, /[\347]/g, // C, c
    ];
    var noaccent = ['A','a','E','e','I','i','O','o','U','u','N','n','C','c'];
     
    var str = this;
    for(var i = 0; i < accent.length; i++){
        str = str.replace(accent[i], noaccent[i]);
    }
     
    return str;
}

function rgb2hex(rgb, noHashtag) {
    if (/^#[0-9A-F]{6}$/i.test(rgb))
        return rgb;

    rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
    function hex(x) {
        return ("0" + parseInt(x).toString(16)).slice(-2);
    }
    return (noHashtag != 'undefined' && noHashtag ? "" : '#') + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
}
// A static class for converting between Decimal and DMS formats for a location
// ported from: http://andrew.hedges.name/experiments/convert_lat_long/
// Decimal Degrees = Degrees + minutes/60 + seconds/3600
// more info on formats here: http://www.maptools.com/UsingLatLon/Formats.html
// use: LocationFormatter.DMSToDecimal( 45, 35, 38, LocationFormatter.SOUTH );
// or:  LocationFormatter.decimalToDMS( -45.59389 );

function LocationFormatter() {
}
;

LocationFormatter.NORTH = 'N';
LocationFormatter.SOUTH = 'S';
LocationFormatter.EAST = 'E';
LocationFormatter.WEST = 'W';

LocationFormatter.roundToDecimal = function (inputNum, numPoints) {
    var multiplier = Math.pow(10, numPoints);
    return Math.round(inputNum * multiplier) / multiplier;
};

LocationFormatter.decimalToDMS = function (location, hemisphere) {
    if (location < 0)
        location *= -1; // strip dash '-'

    var degrees = Math.floor(location);          // strip decimal remainer for degrees
    var minutesFromRemainder = (location - degrees) * 60;       // multiply the remainer by 60
    var minutes = Math.floor(minutesFromRemainder);       // get minutes from integer
    var secondsFromRemainder = (minutesFromRemainder - minutes) * 60;   // multiply the remainer by 60
    var seconds = LocationFormatter.roundToDecimal(secondsFromRemainder, 2); // get minutes by rounding to integer

    return hemisphere + ' ' + degrees + '° ' + minutes + "' " + seconds + "''";
};

LocationFormatter.decimalLatToDMS = function (location) {
    var hemisphere = (location < 0) ? LocationFormatter.SOUTH : LocationFormatter.NORTH; // south if negative
    return LocationFormatter.decimalToDMS(location, hemisphere);
};

LocationFormatter.decimalLongToDMS = function (location) {
    var hemisphere = (location < 0) ? LocationFormatter.WEST : LocationFormatter.EAST;  // west if negative
    return LocationFormatter.decimalToDMS(location, hemisphere);
};

LocationFormatter.DMSToDecimal = function (degrees, minutes, seconds, hemisphere) {
    var ddVal = degrees + minutes / 60 + seconds / 3600;
    ddVal = (hemisphere == LocationFormatter.SOUTH || hemisphere == LocationFormatter.WEST) ? ddVal * -1 : ddVal;
    return LocationFormatter.roundToDecimal(ddVal, 5);
};
function isCoordX(coord) {
//    return /([EW][\s]*[0-1]?\d{1,2}°[\s]+[0-5]?\d'[\s]+[0-5]?\d?\.?\d*'')|([EW][\s]*[0-1]?\d{1,2}[\s]+[0-5]?\d[\s]+[0-5]?\d?\.?\d*)/.test(coord);
    return /[-]?\d+[.]?\d*/.test(coord);
}
function isCoordY(coord) {
//    return /([NS][\s]*\d{1,2}°[\s]+[0-5]?\d'[\s]+[0-5]?\d?\.?\d*'')|([NS][\s]*\d{1,2}[\s]+[0-5]?\d[\s]+[0-5]?\d?\.?\d*)/.test(coord);
    return /[-]?\d+[.]?\d*/.test(coord);
}
function initMap() {
    var latlon = new google.maps.LatLng($('#emplacement_coords_y').val(), $('#emplacement_coords_x').val());
    var mapOptions = {
        center: latlon,
        zoom: 15
    };
    $('#add-work-map').removeClass('hide');
    var map = new google.maps.Map(document.getElementById('add-work-map'), mapOptions);
    var marker = new google.maps.Marker({
        position: latlon,
        map: map,
        title: $('#oeuvre_emplact').val(),
        draggable: true
    });
    /* Draggable marker -> change coords */
    google.maps.event.addListener(marker, 'drag', function (e) {
//        $('#emplacement_coords_y').val(LocationFormatter.decimalLatToDMS(e.latLng.lat()))
//        $('#emplacement_coords_x').val(LocationFormatter.decimalLongToDMS(e.latLng.lng()))
        $('#emplacement_coords_y').val(e.latLng.lat());
        $('#emplacement_coords_x').val(e.latLng.lng());
        $('#oeuvre_emplact').val('');
        $('#oeuvre_id').val('');
    });
    google.maps.event.addListener(marker, 'dragend', function (e) {
//        $('#emplacement_coords_y').val(LocationFormatter.decimalLatToDMS(e.latLng.lat()))
//        $('#emplacement_coords_x').val(LocationFormatter.decimalLongToDMS(e.latLng.lng()))
        $('#emplacement_coords_y').val(e.latLng.lat());
        $('#emplacement_coords_x').val(e.latLng.lng());
        $('#oeuvre_emplact').val('');
        $('#oeuvre_id').val('');
    });
    map.initialize;
}
function initViewWorkMap(x, y, locationLabel) {
    if(typeof google !== 'undefined') {
        if (isCoordX(x) && isCoordY(y)) {
            var latlon = new google.maps.LatLng(y, x);
            var mapOptions = {
                center: latlon,
                zoom: 14
            };
            $('#wv_map').show();
            var map = new google.maps.Map(document.getElementById('wv_map'), mapOptions);
            var marker = new google.maps.Marker({
                position: latlon,
                map: map,
                title: locationLabel
            });
            map.initialize;
        } else {
            $('#wv_map').hide();
        }
    }
}
function hideAddWMap() {
    $('#add-work-map').addClass('hide');
}
function addViewWorkType(id, name, color) {
    $('#work-view #work-types').append('<div data-id="' + id + '">' + name + '</div><br/>');
    $('#work-view #work-types div[data-id="' + id + '"]').css('background-color', '#' + color);
}

function getPageToken() {
    if($('#auth_token_supervisor').length) {
        return $('#auth_token_supervisor').val();
    } else {
        return $('#auth_token_worker').val();
    }
}

function cleanWV() {
    $('#wv_set_urgent').removeClass('active');
    $('#wv_set_normal').removeClass('active');
    $('#wv_set_done').removeClass('active');
    $('#wv_frequency_container').hide();
    $('#wv_tools_container').hide();
    $('#wv_workers_container').hide();
    $('#wv_oeuvre_container').hide();
    $('#wv_workers_container').hide();
    $('#wv_map').hide();
    $('#wv_add_to_ulist').hide();
    $('#wv_remove_from_ulist').hide();
    $('#wv_set_done').hide();
    $('#wv_user_add_container').hide();
    $('#wv_types_container').html('');
    $('#wv_nearby_works_container').hide();
    $('#wv_workers').html('');
    $('#wv_desc_emplact').html('');
    $('#wv_coords').html('');
    $('#wv_nearby_works_container').hide();
    $('#wv_nearby_works').html('');
}

function loadWorkView(workId, browse) {
    
    if(browse) {
        $('#work_view').modal('hide');
    }
    
    $.ajax({
        type: "POST",
        url: "/index.php/ajax/get-work-details",
        data: {
            workId: workId,
            auth_token: getPageToken()
        },
        success: function (response) {
            cleanWV();
            $('#wv_id').val(workId);
            $('#wv_title').html(response.title);
            $('#wv_title_inside').html(response.title);
            if(response.user_add) {
                $('#wv_user_add_container').show();
                $('#wv_user_add').html(response.user_add);
            }
            if (response.prio === "1") {
                $('#wv_set_urgent').addClass('active');
                $('#wv_set_done').show();
            } else if (response.prio === "2") {
                $('#wv_set_normal').addClass('active');
                $('#wv_set_done').show();
            } else {
                $('#wv_set_done').addClass('active');
                $('#wv_set_done').hide();
            }
            fd = response.frequency_days;
            fw = response.frequency_weeks;
            fm = response.frequency_months;
            fcur = null;
            if (fd) {
                fcur = fd;
                if (fd == 1)
                    $('#wv_frequency_type').html('jour');
                else
                    $('#wv_frequency_type').html('jours');
            } else if (fw) {
                fcur = fw;
                if (fd == 1)
                    $('#wv_frequency_type').html('semaine');
                else
                    $('#wv_frequency_type').html('semaines');
            } else if (fm) {
                fcur = fm;
                $('#wv_frequency_type').html('mois');
            }
            if (fcur) {
                $('#wv_frequency_container').show();
                $('#wv_frequency_number').html(fcur);
            }
            if (response.types) {
                $(response.types).each(function (i) {
                    $('#wv_types_container').append('<a class="ui label" style="background-color:#' + this.color + '">' + this.name + '</a>')
                })
            }
            if (response.description) {
                $('#wv_description').html(response.description);
            }
            if (response.tools) {
                $('#wv_tools_container').show();
                $('#wv_tools').html(response.tools);
            }
            if (response.additional_workers.length) {
                $('#wv_workers_container').show();
                $(response.additional_workers).each(function (i) {
                    $('#wv_workers').append('<li>' + this + '</li>')
                })
            }
            if (response.coords_x) {
                x = Math.round(response.coords_x * 10000) / 10000;
                y = Math.round(response.coords_y * 10000) / 10000;
                $('#wv_coords').html(x + ', ' + y);
            }
            if (response.oeuvre_title) {
                $('#wv_oeuvre_container').show();
                $('#wv_oeuvre').html(response.oeuvre_title);
                initViewWorkMap(response.coords_x, response.coords_y, response.oeuvre_title);
            } else if (response.coords_x) {
                initViewWorkMap(response.coords_x, response.coords_y, response.title);
            }
            if (response.desc_emplact) {
                $('#wv_desc_emplact').html(response.desc_emplact);
            }
            if (!response.user_id && response.prio !== "3") {
                $('#wv_add_to_ulist').show();
            } else if(response.user_id && response.user_id === $('#user_id').val()) {
                $('#wv_remove_from_ulist').show();
            }
            if(response.nearby && response.nearby.length) {
                $('#wv_nearby_works_container').show();
                var nbw = $('#wv_nearby_works');
                $(response.nearby).each(function(){
                    ot = '';
                    if(this.oeuvre_title)
                        ot = '('+this.oeuvre_title+')';
                    if(this.distance != 0)
                        nbw.append('<li data-workid="'+this.id+'" class="ui tiny button">'+this.distance+' m : '+this.title+ot+'</li>');
                    else
                        nbw.append('<li data-workid="'+this.id+'" class="ui tiny button">Même endroit : '+this.title+ot+'</li>');
                });
                $('#wv_nearby_works li').click(function(){
                    wid = $(this).attr('data-workid');
                    loadWorkView(wid, true);
                });
            }
            
            $('#work_view').modal({
                onVisible:function(){
                    initViewWorkMap(response.coords_x, response.coords_y, response.title);
                },
                onHidden:function(){
                    cleanWV();
                }
            }).modal('show');
        },
        error: function (response) {
            console.log('AJAX error Get Work');
        }
    });
}

function isWorker() {
    return $('#role_id').val() == $('#worker_role_id').val()
}

function isSupervisor() {
    return $('#role_id').val() == $('#supervisor_role_id').val()
}

function setPrioButtons(list, prio, arrow) {
    var cps = $(list + ' div.change_prio');
    cps.attr('data-prio', prio);
    cps.children('i').removeClass('up down').addClass(arrow);
}

function cleanUserList() {
    $('table.works_table').each(function(){
        if(!$(this).children('tbody').children('tr').length) {
            $(this).prev('div.label').remove();
            $(this).remove();
        }
    });
}

function cleanIconsPrioList() {
    $('table.works_table tr').each(function(){
        var pinI = $(this).children('td.item').children('i.pin');
        var lockI = $(this).children('td.item').children('i.lock');
        var alertI = $(this).children('td.item').children('i.warning.sign');
        var state = $(this).attr('data-workstate');
        var alert = $(this).attr('data-alert');
        if(state === "current") {
            lockI.hide();
        } else if(state === "other") {
            pinI.hide();
        } else {
            lockI.hide();
            pinI.hide();
        }
        if(!alert) {
            alertI.hide();
        } else {
            alertI.show();
        }
    });
}

function refreshUListCount() {
    $.ajax({
        type: "GET",
        url: '/index.php/ajax/get-ulist-count',
        data: {
            uid: $('#user_id').val(),
            auth_token: getPageToken()
        },
        success: function (response) {
            if(response)
                $('#ulist_count').html(response.works_count)
            else
                $('#ulist_count').html('0')
        },
        error: function (response) {
            console.log('AJAX error: get ulist count');
        }
    });
}


function refreshStats() {
    $.ajax({
        type: "GET",
        url: '/index.php/ajax/get-works-stats',
        data: {
            auth_token: getPageToken()
        },
        success: function (response) {
            $('#urgent_count').html(response.urgent_works);
            $('#ten_days_or_less_count').html(response.ten_days_or_less_works);
            $('#late_count').html(response.late_works);
            $('#affectation_ratio').html(response.affectation_ratio);
            $('#affectation_ratio_urgent').html(response.affectation_ratio_urgent);
            if(response.ten_days_or_less_works > 10)
                $('#ten_days_or_less_count').addClass('alert');
            else
                $('#ten_days_or_less_count').removeClass('alert');
            if(response.late_count > 0)
                $('#late_count').addClass('alert');
            else
                $('#late_count').removeClass('alert');
            if(response.affectation_ratio_urgent < 100)
                $('#affectation_ratio_urgent').addClass('alert');
            else
                $('#affectation_ratio_urgent').removeClass('alert');
        },
        error: function (response) {
            console.log(response)
            console.log('AJAX error: get works stats');
        }
    });
}

function refreshWorkDays(wid) {
    $.ajax({
        type: "GET",
        url: '/index.php/ajax/get-work-daysto',
        data: {
            wid: wid,
            auth_token: getPageToken()
        },
        success: function (response) {
            var wr = $('tr[data-workid="' + wid + '"] .days_to');
            if(response.days_to <= 3) {
                wr.addClass('urgent');
            } else {
                wr.removeClass('urgent');
            }
            wr.html(response.str);
        },
        error: function () {
            console.log('AJAX error: get work daysto');
        }
    });
}

function removeUList(wid, uid, context) {
    var tr = $('tr[data-workid="'+wid+'"]');
    var bRem = tr.find('div.remove_ulist');
    var bAdd = tr.find('div.add_ulist');
    var pinI = tr.children('td.item').children('i.pin');
    $.ajax({
        type: "GET",
        url: '/index.php/ajax/remove-from-ulist',
        data: {
            wid: wid,
            uid: uid,
            auth_token: getPageToken()
        },
        success: function () {
            tr.attr('data-workstate', 'free');
            bAdd.show();
            bRem.hide();
            pinI.hide();
            refreshUListCount();
            refreshStats();
            if(context === 'wv') {
                $('#wv_remove_from_ulist').hide();
                $('#wv_add_to_ulist').show();
            }
            if($('.liste_perso').length) {
                document.location.href="/travaux/liste-perso";
            }
            wstate = tr.attr('data-workstate');
            if(wstate == "free")
                tr.find('.icon.warning.sign').show();
        },
        error: function () {
            console.log('AJAX error: remove ulist');
        }
    });
}

function addUList(wid, uid, context) {
    var tr = $('tr[data-workid="'+wid+'"]');
    var bRem = tr.find('div.remove_ulist');
    var bAdd = tr.find('div.add_ulist');
    var pinI = tr.children('td.item').children('i.pin');
    $.ajax({
        type: "GET",
        url: '/index.php/ajax/add-to-ulist',
        data: {
            wid: wid,
            uid: uid,
            auth_token: getPageToken()
        },
        success: function () {
            // Afficher seulement "-"
            // tr.data-workstate="current"
            tr.attr('data-workstate', 'current');
            bAdd.hide();
            bRem.show();
            pinI.show();
            refreshUListCount();
            refreshStats();
            if(context === 'wv') {
                $('#wv_remove_from_ulist').show();
                $('#wv_add_to_ulist').hide();
            }
            if($('.liste_perso').length) {
                document.location.href="/travaux/liste-perso";
            }
            tr.find('.icon.warning.sign').hide();
        },
        error: function () {
            console.log('AJAX error: add ulist');
        }
    });
}

$(document).ready(function () {
    $(document).tooltip({track: true});
    $('.works_table').tablesorter({
        sortList: [[0,0]]
    });
    $('.hide').hide();
    if(isWorker()) {
        $('#wv_options').hide();
    }
    $('.ui.dropdown').dropdown({
        allowCategorySelection: true
    });
    $('.works_table .buttons').hide();
    $('table.works_table td.item').click(function () {
        loadWorkView($(this).parent('tr').attr('data-workid'));
    });
    $('table.works_table td.work_title').click(function () {
        loadWorkView($(this).parent('tr').attr('data-workid'));
    });
    $('table.works_table td.oeuvre').click(function () {
        loadWorkView($(this).parent('tr').attr('data-workid'));
    });
    $('table.works_table td.types').click(function () {
        loadWorkView($(this).parent('tr').attr('data-workid'));
    });
    $('table.works_table tr').hover(function(){
        $(this).find('.buttons').show();
    },function(){
        $(this).find('.buttons').hide();
    });
    
    $('.clickable_link').click(function () {
        document.location.href = $(this).attr('data-href');
    });
    // Modals
    $('.ui.modal').modal();

    // Notices
    $('#noticesContainer .message').show();
    $('#noticesContainer .message').delay(3000).fadeOut();

    cleanIconsPrioList();
    refreshStats();

    // +/- refresh
    $('.works_table tr').each(function(){
        var s = $(this).attr('data-workstate');
        if(s === "current") {
            // Afficher -
            $(this).find('div.add_ulist').hide();
        } else if(s === "other") {
            // Ne rien afficher
            $(this).find('div.add_ulist').hide();
            $(this).find('div.remove_ulist').hide();
        } else if(s === "free") {
            // Afficher +
            $(this).find('div.remove_ulist').hide();
        }
    });

    $('.delete_work_button').click(function () {
        $('input#waiting_action').attr('data-href', $(this).attr('data-href'));
        $('#delete_work_modal')
                .modal({
                    onApprove: function () {
                        document.location.href=$('input#waiting_action').attr('data-href');
                    }
                })
                .modal('show');
    });
    $('#wv_print_work_button').click(function(){
        $('#wv_map').hide();
        $('.buttons').hide();
        $('.actions').hide();
        $('#work_view').addClass('printing');
        html2canvas($('#work_view'), {
            useCors: true,
            onrendered: function(canvas) {
               var myImage = canvas.toDataURL("image/png");
               var tWindow = window.open(""); 
                $(tWindow.document.body).html("<img id='Image' src=" + myImage + " style='width:100%;'></img>").ready(function () {
                    tWindow.focus();
                    tWindow.print();
                    tWindow.close();
                });
            }
        });
        $('#work_view').removeClass('printing');
        $('#wv_map').show();
        $('.buttons').show();
        $('.actions').show();
    });
    $('#wv_edit_work_button').click(function(){
        document.location.href='/travaux/editer/id/'+$('#wv_id').val();
    });
    $('#wv_remove_work_button').click(function () {
        $('input#waiting_action').attr('data-href', '/travaux/supprimer/id/' + $('#wv_id').val());
        $('#delete_work_modal')
                .modal({
                    onApprove: function () {
                        document.location.href=$('input#waiting_action').attr('data-href');
                    }
                })
                .modal('show');
    });
    $('#wv_set_urgent').click(function () {
        wid = $('#wv_id').val();
        $.ajax({
            type: "GET",
            url: '/index.php/ajax/change-work-prio',
            data: {
                id: wid,
                p: 1,
                auth_token: getPageToken()
            },
            success: function (response) {
                $('#wv_set_urgent').addClass('active');
                $('#wv_set_normal').removeClass('active');
                $('#wv_set_done').removeClass('active');
                wm = $('tr[data-workid="' + wid + '"]').detach();
                $('#works_1').append(wm);
                wm.find('div.set_work_done_button').show();
                refreshWorkDays(wid);
                wstate = wm.attr('data-workstate');
                if(wstate == "free")
                    wm.find('.icon.warning.sign').show();
                $('#works_1').trigger("update"); 
                setPrioButtons('#works_1', 2, 'down');
                refreshStats();
                wm.find('.buttons').hide();
            },
            error: function (response) {
                console.log('AJAX error: wv_set_urgent');
            }
        });
    });
    $('#wv_set_normal').click(function () {
        wid = $('#wv_id').val();
        $.ajax({
            type: "GET",
            url: '/index.php/ajax/change-work-prio',
            data: {
                id: wid,
                p: 2,
                auth_token: getPageToken()
            },
            success: function (response) {
                $('#wv_set_urgent').removeClass('active');
                $('#wv_set_normal').addClass('active');
                $('#wv_set_done').removeClass('active');
                wm = $('tr[data-workid="' + wid + '"]').detach();
                wm.find('div.set_work_done_button').show();
                $('#works_2').append(wm);
                refreshWorkDays(wid);
                wm.find('.icon.warning.sign').hide();
                $('#works_2').trigger("update"); 
                setPrioButtons('#works_2', 1, 'up');
                refreshStats();
                wm.find('.buttons').hide();
            },
            error: function (response) {
                console.log('AJAX error: wv_set_normal');
            }
        });
    });
    $('#wv_set_done').click(function () {
        wid = $('#wv_id').val();
        $.ajax({
            type: "GET",
            url: '/index.php/ajax/set-work-done',
            data: {
                id: wid,
                auth_token: getPageToken()
            },
            success: function (response) {
                $('#wv_set_urgent').removeClass('active');
                $('#wv_set_normal').removeClass('active');
                $('#wv_set_done').addClass('active');
                wm = $('tr[data-workid="' + wid + '"]').detach();
                wm.find('.add_ulist').hide();
                wm.find('.remove_ulist').hide();
                wm.find('.pin').hide();
                $('#works_3').append(wm);
                refreshWorkDays(wid);
                wm.find('.icon.warning.sign').hide();
                $('#works_3').trigger("update"); 
                setPrioButtons('#works_3', 2, 'up');
                refreshStats();
                wm.find('.buttons').hide();
                clearDaysToDoneWorks();
                if(isWorker()) {
                    $('#wv_set_done').hide();
                    cleanUserList();
                    refreshUListCount();
                }
                $('#work_view').transition('tada');
            },
            error: function (response) {
                console.log('AJAX error: wv_set_done');
            }
        });
    });
    $('.set_work_done_button').click(function () {
        wid = $(this).parents('tr').attr('data-workid');
        $('#set_work_done_modal')
                .modal({
                    onApprove: function () {
                        $.ajax({
                            type: "GET",
                            url: '/index.php/ajax/set-work-done',
                            data: {
                                id: wid,
                                auth_token: getPageToken()
                            },
                            success: function (response) {
                                wm = $('tr[data-workid="' + wid + '"]').detach();
                                $('#works_3').append(wm);
                                $('#works_3').trigger("update"); 
                                refreshWorkDays(wid);
                                setPrioButtons('#works_3', 2, 'up');
                                refreshStats();
                                wm.find('.buttons').hide();
                                if($('#auth_token_supervisor').val()) { 
                                    $('tr[data-workid="' + wid + '"]').find('i.icon.warning.sign').hide();
                                    $('tr[data-workid="' + wid + '"]').find('i.icon.lock').hide();
                                    $('tr[data-workid="' + wid + '"]').find('div.set_work_done_button').hide();
                                } else {
                                    $('tr[data-workid="' + wid + '"]').find('i.icon.warning.sign').hide();
                                    $('tr[data-workid="' + wid + '"]').find('i.icon.pin').hide();
                                    $('tr[data-workid="' + wid + '"]').find('i.icon.lock').hide();
                                    $('tr[data-workid="' + wid + '"]').find('.buttons').hide();
                                }
                                if(isWorker()) {
                                    cleanUserList();
                                    refreshUListCount();
                                }
                            },
                            error: function (response) {
                                console.log('AJAX error: set_done');
                            }
                        });
                    }
                })
                .modal('show');
    });
    $('.add_ulist').click(function () {
        wid = $(this).parents('tr').attr('data-workid');
        uid = $('#user_id').val();
        
        addUList(wid, uid, 'list')
    });
    $('.remove_ulist').click(function () {
        wid = $(this).parents('tr').attr('data-workid');
        uid = $('#user_id').val();
        
        removeUList(wid, uid, 'list');        
    });
    
    $('#wv_add_to_ulist').click(function(){
        addUList($('#wv_id').val(), $('#user_id').val(), 'wv');
    });
    
    $('#wv_remove_from_ulist').click(function(){
        removeUList($('#wv_id').val(), $('#user_id').val(), 'wv');
    });

    $('section#works-list li').click(function () {
        console.log('ID : ' + $(this).parents('tr').attr('data-workid'));
        loadWorkView($(this).parents('tr').attr('data-workid'));
        $('div#noticesContainer dialog').hide();
    });

    $('div.change_prio').click(function () {
        wid = $(this).parents('tr').attr('data-workid');
        prio = $(this).attr('data-prio');
        $.ajax({
            type: "GET",
            url: '/index.php/ajax/change-work-prio',
            data: {
                id: wid,
                p: prio,
                auth_token: getPageToken()
            },
            success: function (response) {
                wm = $('tr[data-workid="' + wid + '"]').detach();
                $('#works_' + prio).append(wm);
                $('#works_' + prio).trigger("update"); 
                refreshWorkDays(wid);
                wm.find('.buttons').hide();
                setPrioButtons('#works_1', 2, 'down');
                setPrioButtons('#works_2', 1, 'up');
                wm.hide().transition('pulse');
                if(1 == prio || 2 == prio) {
                    wm.find('div.set_work_done_button').show();
                }
                refreshStats();
                if(prio == 1 && wm.attr('data-workstate') == 'free') {
                    wm.find('i.warning.sign').show();
                } else {
                    wm.find('i.warning.sign').hide();
                }
            },
            error: function (response) {
                console.log('AJAX error: wv_set_urgent');
            }
        });
    })

    if ($('#formEditUser').length > 0) {
        $('#edit_user').click(function () {
            $('#formEditUser').submit();
        })
    }
    if ($('#formAddUser').length) {
        $('#add_new_user').click(function(){
            $('#formAddUser').submit();
        });
    }
    if ($('#formEditType').length > 0) {
        $('#edit_type').click(function () {
            $('#formEditType').submit();
        })
        $('#add_type_color_btn').colpick({onSubmit: function (hsb, hex, rgb, el) {
            $(el).css('background-color', '#' + hex);
            $(el).colpickHide();
            $('#type_color').val(hex);
        }});
    }
    if ($('#formAddType').length) {
        $('#add_new_type').click(function(){
            $('#formAddType').submit();
        });
        $('#add_type_color_btn').colpick({onSubmit: function (hsb, hex, rgb, el) {
            $(el).css('background-color', '#' + hex);
            $(el).colpickHide();
            $('#type_color').val(hex);
        }});
    }
    if ($('#formAddOeuvre').length) {
        $('#add_new_oeuvre').click(function(){
            $('#formAddOeuvre').submit();
        });
    }
    if ($('#formEditOeuvre').length > 0) {
        $('#edit_oeuvre').click(function () {
            $('#formEditOeuvre').submit();
        })
    }
    if($('#usersList').length) {
        $('.delete_user_button').click(function() {
            $('input#waiting_action').attr('data-href', $(this).attr('data-href'));
            $('#delete_user_modal')
                .modal({
                    onApprove: function () {
                        document.location.href=$('input#waiting_action').attr('data-href');
                    }
                })
                .modal('show');
        });
        $('.edit_user_button').click(function() {
            document.location.href=$(this).attr('data-href');
        });
    }
    if($('#oeuvresList').length) {
        $('.delete_oeuvre_button').click(function() {
            $('input#waiting_action').attr('data-href', $(this).attr('data-href'));
            $('#delete_oeuvre_modal')
                .modal({
                    onApprove: function () {
                        document.location.href=$('input#waiting_action').attr('data-href');
                    }
                })
                .modal('show');
        });
        $('.edit_oeuvre_button').click(function() {
            document.location.href=$(this).attr('data-href');
        });
    }
    if($('#typesList').length) {
        $('.delete_type_button').click(function() {
            $('input#waiting_action').attr('data-href', $(this).attr('data-href'));
            $('#delete_type_modal')
                .modal({
                    onApprove: function () {
                        document.location.href=$('input#waiting_action').attr('data-href');
                    }
                })
                .modal('show');
        });
        $('.edit_type_button').click(function() {
            document.location.href=$(this).attr('data-href');
        });
    }
    if ($('.formWork').length > 0) {
        hideAddWMap();
        var wt = $('[name="worktype"][checked="checked"]').val();
        $('#add_edit_work').click(function () {
            $('#formAddWork').submit();
        })
        if (!isCoordX($('#emplacement_coords_x').val()) || !isCoordY($('#emplacement_coords_y').val())) {
            hideAddWMap();
        } else {
            initMap();
        }
        if ($('#maponload').val() == 1) {
            initMap();
        }
        $('.prevAddWorker').each(function (idx, elt) {
            var curIdx = 'additional-worker-' + ($('.additional_worker').length + 1);
            var adW = $('#additional_worker_template')
                    .clone()
                    .prop('id', 'additional-workers[]')
                    .prop('name', 'additional-workers[]');
            adW.show()
                    .addClass('additional_worker')
                    .val(elt.value)
                    .insertBefore('#add_additional_worker')
                    .keyup(function () {
                        var hasEmptyValues = false;
                        for (var i = 0; i < $('.additional_worker').length; i++) {
                            if ($('.additional_worker')[i].value === '') {
                                hasEmptyValues = true;
                                break;
                            }
                        }
                        if (!hasEmptyValues) {
                            $('#add_additional_worker').show();
                        } else {
                            $('#add_additional_worker').hide();
                        }
                    });
        });
        $('.prevAddWorker').remove();
        $('input#oeuvre_emplact').autocomplete({
            source: function (request, response) {
                $.ajax({
                    type: "GET",
                    url: '/index.php/ajax/get-oeuvres',
                    data: {
                        q: request.term,
                        auth_token: getPageToken()
                    },
                    dataType: 'json',
                    async: true,
                    cache: true,
                    success: function (data) {
                        var suggestions = [];
                        $.each(data, function (i, val) {
                            suggestions.push({'id': val.Value, 'value': val.Text});
                        });
                        response(suggestions);
                    },
                    error: function (response) {
                        console.log('AJAX error: get-oeuvres');
                    }
                });
            },
            select: function (event, ui) {
                $('#oeuvre_id').val(ui.item.id);
                $.ajax({
                    type: "POST",
                    url: '/index.php/ajax/get-oeuvre-coords',
                    data: {
                        oeuvreId: ui.item.id,
                        auth_token: getPageToken()
                    },
                    success: function (response) {
                        if (response.coords_x && response.coords_y) {
                            $('#emplacement_coords_x').val(response.coords_x);
                            $('#emplacement_coords_y').val(response.coords_y);
                            initMap();
                        }
                        else {
                            $('#emplacement_coords_x').val('');
                            $('#emplacement_coords_y').val('');
                            hideAddWMap();
                        }
                        //refreshMap();
                    },
                    error: function (response) {
                        console.log('AJAX error: get-oeuvres-coords');
                    }
                });
            }
        });
        $('input#oeuvre_emplact').on('keyup change', function () {
            if ($(this).val() === '') {
                $('#oeuvre_id').val('');
                $('#emplacement_coords_x').val('');
                $('#emplacement_coords_y').val('');
            }
        });
        $('input#emplacement_coords_x').on('keyup change', function () {
            $('#oeuvre_id').val('');
            $('input#oeuvre_emplact').val('');
            var coord_x = $('input#emplacement_coords_x').val();
            var coord_y = $('input#emplacement_coords_y').val();
            if (isCoordX(coord_x) && isCoordY(coord_y))
                initMap();
            else
                hideAddWMap();
        });
        $('input#emplacement_coords_y').on('keyup change', function () {
            $('#oeuvre_id').val('');
            $('input#oeuvre_emplact').val('');
            var coord_x = $('input#emplacement_coords_x').val();
            var coord_y = $('input#emplacement_coords_y').val();
            if (isCoordX(coord_x) && isCoordY(coord_y))
                initMap();
            else
                hideAddWMap();
        });

        $('#add_type_color_btn').colpick({onSubmit: function (hsb, hex, rgb, el) {
                $(el).css('background-color', '#' + hex);
                $(el).colpickHide();
            }});

        $('#add_additional_worker').click(function () {
            var curIdx = 'additional-worker-' + ($('.additional_worker').length + 1);
            var adW = $('#additional_worker_template')
                    .clone()
                    .prop('id', 'additional-workers[]')
                    .prop('name', 'additional-workers[]');
            adW.show()
                    .addClass('additional_worker')
                    .insertBefore('#add_additional_worker')
                    .keyup(function () {
                        var hasEmptyValues = false;
                        for (var i = 0; i < $('.additional_worker').length; i++) {
                            if ($('.additional_worker')[i].value === '') {
                                hasEmptyValues = true;
                                break;
                            }
                        }
                        if (!hasEmptyValues) {
                            $('#add_additional_worker').show();
                        } else {
                            $('#add_additional_worker').hide();
                        }
                    });
            $(this).hide();
        });
        $('#add_type_btn').click(function () {
            $.ajax({
                type: "POST",
                url: "/index.php/ajax/create-on-fly-type",
                data: {
                    name: $('#add_type_label').val(),
                    color: rgb2hex($('#add_type_color_btn').css('background-color'), true),
                    auth_token: getPageToken()
                },
                success: function (response) {
                    if (response.success == true) {
                        function addListElement(el) {
                            $('dd#types-element').append(el);
                            var labels = $('dd#types-element label').sort(function (a, b) {
                                if ($(a).text() < $(b).text())
                                    return -1;
                                if ($(a).text() > $(b).text())
                                    return 1;
                                return 0;
                            });
                            $('dd#types-element').html(labels);
                            $('dd#types-element label.hide').removeClass('hide');
                        }
                        addListElement('<label class="hide"><input type="checkbox" checked="checked" name="types[]" id="types-' + response.typeId + '" value="' + response.typeId + '">' + response.typeName + '</label>');
                    }
                    else if (response.error == true && response.typeId) {
                        $('input#types-' + response.typeId).prop('checked', true);
                    }
                    if (response.notice) {
                        $('div#noticesContainer').empty().prepend(response.notice);
                        $('#noticesContainer .message').show();
                        $('#noticesContainer .message').delay(3000).fadeOut();
                    }
                },
                error: function (response) {
                    console.log(response);
                    console.log('AJAX error');
                }
            });
        });
        $('#add_type_label').keypress(function (e) {
            if (e.which == 13) {
                $('#add_type_btn').click();
                e.preventDefault();
            }
        });
        $('#manage_types_btn').click(function () {
            window.location.href = '/index.php/types/';
        });
        $('input[name="worktype"]').change(function () {
            var wtype = $(this).val();
            $('#title').removeClass('hide');
        });
    }
});