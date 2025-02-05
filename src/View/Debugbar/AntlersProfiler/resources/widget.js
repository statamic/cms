(function ($) {
    var csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-'),
        performanceCategories = {
            executionTimeGreat: 1,
            executionTimeGood: 2,
            executionTimeWarning: 3,
            executionTimeStrongWarning: 4,
            executionTimeDanger: 5,
        },
        colors = {
            hotCode: '#f6eec7',
            sgFast: '#bbffbb',
            sgGood: '#86f286',
            sgWarn: '#f2dea1',
            sgStrongWarn: '#f8c7b4',
            sgBad: '#f1a7a7',
        },
        svgs = {
            codeBrackets: `<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke-width="1.5" class="antlers-trace-svg">
    <path d="M22.5,21.753a1.5,1.5,0,0,1-1.5,1.5H3a1.5,1.5,0,0,1-1.5-1.5V2.253A1.5,1.5,0,0,1,3,.753H18.045a1.5,1.5,0,0,1,1.048.427l2.955,2.882A1.5,1.5,0,0,1,22.5,5.136Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
    <path d="M14.295 9.003L18.045 12.753 14.295 16.503" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path><path d="M9.795 9.003L6.045 12.753 9.795 16.503" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
</svg>
`,
            hotCode: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="antlers-trace-svg antlers-hot-code">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" />
  <path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 00.495-7.467 5.99 5.99 0 00-1.925 3.546 5.974 5.974 0 01-2.133-1A3.75 3.75 0 0012 18z" />
</svg>
`,
            warning: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="antlers-trace-svg antlers-warning">
  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
</svg>
`,
            tag: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="antlers-trace-svg antlers-tag">
  <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
  <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
</svg>
`,
            externalLink: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="antlers-trace-svg antlers-external-link">
  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
</svg>
`,
            variable: `<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke-width="1.5" class="antlers-trace-svg">
    <path d="M16.5,3.75H10.8a3.3,3.3,0,0,0-3.3,3.3c0,4.95,9,4.95,9,9.9a3.3,3.3,0,0,1-3.3,3.3H7.5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 3.75L12 0.75" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
    <path d="M12 20.25L12 23.25" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
</svg>
`,
        };

    var categoryColorMapping = {};
    categoryColorMapping[performanceCategories.executionTimeGreat] = colors.sgFast;
    categoryColorMapping[performanceCategories.executionTimeGood] = colors.sgGood;
    categoryColorMapping[performanceCategories.executionTimeWarning] = colors.sgWarn;
    categoryColorMapping[performanceCategories.executionTimeStrongWarning] = colors.sgStrongWarn;
    categoryColorMapping[performanceCategories.executionTimeDanger] = colors.sgBad;

    function getSampleTime(originalTime) {
        var date = new Date(originalTime),
            seconds = date.getSeconds().toString().padStart(2, '0'),
            milliseconds = date.getMilliseconds().toString().padStart(3, '0');

        return `${seconds}:${milliseconds}`;
    }

    function truncateString(str, maxLength) {
        if (str.length <= maxLength) {
            return str;
        }

        return str.slice(0, maxLength) + '...';
    }

    function formatBytes(bytes) {
        if (bytes < 1024) {
            return bytes + ' bytes';
        } else if (bytes < 1024 * 1024) {
            return (bytes / 1024).toFixed(2) + ' KB';
        } else if (bytes < 1024 * 1024 * 1024) {
            return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
        } else if (bytes < 1024 * 1024 * 1024 * 1024) {
            return (bytes / (1024 * 1024 * 1024)).toFixed(2) + ' GB';
        }

        return (bytes / (1024 * 1024 * 1024 * 1024)).toFixed(2) + ' TB';
    }

    function makeEditorLink(data) {
        return `<a href="${data.editorLink}" class="antlers-trace-editor-link" title="Open in editor">${svgs.externalLink}</a>`;
    }

    function removeEmptyChildren(obj) {
        if (Array.isArray(obj.children)) {
            obj.children.forEach(function (child) {
                removeEmptyChildren(child);
            });
            if (obj.children.length === 0) {
                delete obj.children;
            }
        }
    }

    var AntlersWidget = (PhpDebugBar.Widgets.StatamicAntlersWidget = PhpDebugBar.Widget.extend({
        className: csscls('antlers'),

        render: function () {
            this.bindAttr(
                'data',
                function (data) {
                    var debugWarningLabel = '';

                    if (data.had_active_debug_sessions) {
                        debugWarningLabel = `<div id="antlers-trace-debugger-warning" class="antlers-trace-metric" title="An Antlers debugger was active and may influence results." style="display:none">
    <span id="antlers-trace-debugger-symbol"></span>
    <span>Debugger Attached</span>
</div>`;
                    }

                    var $content = $(`
<div id="antlers-trace-toolbar">
    <h2>Antlers Profiler [Experimental]</h2>
    <div id="antlers-trace-metrics">
        ${debugWarningLabel}
    </div>
    <div>
        <button title="Display nested performance data by view file." id="btn-antlers-trace-view-graph" class="antlers-trace-button antlers-trace-button-active">View Graph</button>
        <button title="Display performance data for each Antlers expression." id="btn-antlers-trace-expression-graph" class="antlers-trace-button">Expression Graph</button>
        <button title="View generated source output with performance data." id="btn-antlers-trace-source-graph" class="antlers-trace-button">Source View</button>
    </div>
</div>

<div id="antlers-trace-report-view">
    <div id="antlers-trace-reports">
        <div id="antlers-trace-call-report">
            <div style="width:100%;height:150px;">
                <canvas id="antlers-trace-chart" style="height: 100px;"></canvas>
            </div>

            <div id="antlers-trace-call-table">
            </div>
        </div>

        <div id="antlers-trace-source-graph" style="display:none;">

        </div>
    </div>
    <div id="antlers-trace-properties" style="display:none;">
        <div id="antlers-trace-properties-toolbar">
            <span>Details</span>
            <button id="btn-antlers-trace-close-properties">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="antlers-trace-svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div id="antlers-trace-properties-content">

        </div>
    </div>
</div>
`);

                    this.$el.empty().append($content);

                    var btnCallGraph = document.getElementById('btn-antlers-trace-view-graph'),
                        btnSourceGraph = document.getElementById('btn-antlers-trace-source-graph'),
                        btnNodeGraph = document.getElementById('btn-antlers-trace-expression-graph'),
                        btnCloseProperties = document.getElementById('btn-antlers-trace-close-properties'),
                        tabCallGraph = document.getElementById('antlers-trace-call-report'),
                        tabSourceGraph = document.getElementById('antlers-trace-source-graph'),
                        $sourceGraph = $(tabSourceGraph),
                        metricsContainer = document.getElementById('antlers-trace-metrics'),
                        $metricsContainer = $(metricsContainer),
                        antlersTracePanel = document.querySelector('.phpdebugbar-widgets-antlers'),
                        debugBarPanel = antlersTracePanel.closest('.phpdebugbar-panel'),
                        debugBarBody = document.querySelector('.phpdebugbar-body'),
                        reportViewElement = document.getElementById('antlers-trace-reports'),
                        propertiesView = document.getElementById('antlers-trace-properties'),
                        reportTable = document.getElementById('antlers-trace-call-table'),
                        propertiesContent = document.getElementById('antlers-trace-properties-content'),
                        $propertiesContent = $(propertiesContent);

                    function hidePropertiesWindow() {
                        propertiesView.style.display = 'none';
                    }

                    function openPropertiesWindow(obj) {
                        var objProperties = [],
                            properties = '';
                        objProperties.push({
                            label: 'Expression Content',
                            value: `<span style="font-family:monospace">${obj.escapedNodeContent}</span>`,
                            help: '',
                        });

                        objProperties.push({
                            label: 'Source File',
                            value: `<span style="font-family:monospace">${obj.path}</span>`,
                            help: '',
                        });

                        if (obj.line > 0) {
                            objProperties.push({
                                label: 'Line Number',
                                value: `<span style="font-family:monospace">${obj.line}</span>`,
                                help: '',
                            });
                        }

                        objProperties.push({
                            label: 'Self Execution Time',
                            value: obj.clientSelfTimeDisplay,
                            help: 'The time spent evaluating this Antlers expression.',
                        });

                        objProperties.push({
                            label: 'Total Execution Time',
                            value: obj.clientTotalTimeDisplay,
                            help: 'The time spent evaluating the Antlers expression, and all child expressions.',
                        });

                        objProperties.push({
                            label: 'Execution Time %',
                            value: obj.percentOfExecutionTime,
                            help: 'The percent of rendering time attributable to this Antlers expression.',
                        });

                        objProperties.push({
                            label: '# of Executions',
                            value: obj.executionCount,
                            help: 'The total number of times this Antlers expression was evaluated.',
                        });

                        objProperties.push({
                            label: 'Cumulative Memory',
                            value: formatBytes(obj.cumulativeMemorySamples),
                            help: 'The relative amount of memory consumed while this Antlers expression was being evaluated.',
                        });

                        if (obj.escapedSourceContent.trim().length > 0) {
                            objProperties.push({
                                label: 'Inner Expression Content',
                                value: `<span style="font-family:monospace">${obj.escapedSourceContent}</span>`,
                                help: '',
                            });
                        }

                        objProperties.forEach((prop) => {
                            var helpText = '';

                            if (prop.help.length > 0) {
                                helpText = `<span class="antlers-trace-property-desc">${prop.help}</span>`;
                            }

                            properties += `<div class="antlers-trace-property">
    <span class="antlers-trace-property-key">${prop.label}</span>
    <span class="antlers-trace-property-value">${prop.value}</span>
    ${helpText}
</div>
`;
                        });

                        var template = `<div class="antlers-trace-properties-window">${properties}</div>`;

                        $propertiesContent.empty().append($(template));

                        propertiesView.style.display = 'initial';
                    }

                    btnCloseProperties.addEventListener('click', function () {
                        hidePropertiesWindow();
                    });

                    function matchHeights() {
                        var targetHeight = debugBarBody.offsetHeight - 28;
                        reportViewElement.style.height = targetHeight + 'px';
                        propertiesView.style.height = targetHeight - 3 + 'px';

                        var chartHeight = chartContainer.offsetHeight;

                        reportTable.style.height = targetHeight - chartHeight - 4 + 'px';
                    }

                    debugBarPanel.style.overflow = 'hidden';

                    function addMetric(name, display) {
                        var metricContainer = $(`<div class="antlers-trace-metric">
    <span class="antlers-trace-metric-name">${name}:</span>
    <span class="antlers-trace-metric-display">${display}</span>
</div`);

                        $metricsContainer.append(metricContainer);
                    }

                    $sourceGraph.empty();

                    data.source_samples.forEach((item) => {
                        var backgroundColor = 'transparent',
                            color = 'black',
                            title = '',
                            cursorStyle = '';

                        if (item.isNodeObject && item.totalElapsedTime != null && item.totalElapsedTime >= 0) {
                            backgroundColor = categoryColorMapping[item.executionTimeCategory];

                            title = `title = "${item.clientTotalTimeDisplay}`;

                            if (item.escapedNodeContent.trim().length > 0) {
                                title += `: ${item.escapedNodeContent}`;
                            }

                            title += '"';
                            cursorStyle = 'cursor:pointer;';
                        }

                        if (item.isCloseOutput) {
                            color = 'gray';
                        }

                        var template = `<pre ${title} style="display:inline; line-height: 2; color: ${color}; background-color:${backgroundColor};${cursorStyle}">${item.escapedBufferOutput}</pre>`;

                        if (!item.isNodeObject) {
                            $sourceGraph.append(template);
                        } else {
                            $nodeEl = $(template)[0];
                            $nodeEl.addEventListener('click', function () {
                                openPropertiesWindow(item);
                            });

                            $sourceGraph.append($nodeEl);
                        }
                    });

                    var chartContainer = document.getElementById('antlers-trace-chart'),
                        chartData = {
                            labels: data.system_samples.map((obj) => obj.time),
                            datasets: [
                                {
                                    label: 'Memory Usage (MB)',
                                    data: data.system_samples.map((obj) => ({
                                        x: obj.time,
                                        y: obj.memory / (1024 * 1024),
                                    })),
                                    fill: {
                                        target: 'origin',
                                        above: 'rgb(94 190 255)',
                                        below: '#3498db',
                                    },
                                },
                            ],
                        };

                    new Chart(chartContainer, {
                        type: 'line',
                        height: 100,
                        data: chartData,
                        options: {
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    ticks: {
                                        callback: function (value, index, values) {
                                            return getSampleTime(data.system_samples[index].time);
                                        },
                                    },
                                },
                                y: {
                                    beginAtZero: true,
                                },
                            },
                        },
                    });

                    var columnConfig = {
                        expander: {
                            title: '',
                        },
                        sequence: {
                            title: 'Time',
                            field: 'sampleTime',
                            formatter: function (cell) {
                                var data = cell.getData();

                                return getSampleTime(data.sampleTime);
                            },
                        },
                        type: {
                            title: 'Type',
                            field: 'type',
                            formatter: function (cell) {
                                var data = cell.getData(),
                                    contents = '',
                                    icon = '';

                                if (!data.isNodeObject) {
                                    contents = 'View';
                                    icon = svgs.codeBrackets;
                                } else if (data.isTag) {
                                    contents = 'Tag';
                                    icon = svgs.tag;
                                } else {
                                    contents = 'Variable';
                                    icon = svgs.variable;
                                }

                                return `<span class="antlers-trace-type-tooltip">${icon}${contents}</span>`;
                            },
                        },
                        item: {
                            title: 'Item',
                            maxInitialWidth: 640,
                            field: 'clientDisplay',
                            formatter: function (cell) {
                                var data = cell.getData();
                                var contents = !data.isNodeObject ? data.path : data.escapedNodeContent;

                                contents = truncateString(contents, 100);

                                return `<span class="antlers-trace-item-tooltip">${contents}</span>`;
                            },
                        },
                        path: {
                            title: 'View Path',
                            field: 'path',
                            visible: false,
                            formatter: function (cell) {
                                var data = cell.getData(),
                                    output = data.path;

                                if (data.editorLink.trim().length > 0) {
                                    output += makeEditorLink(data);
                                }

                                return output;
                            },
                        },
                        lineNumber: {
                            title: 'Line',
                            headerHozAlign: 'right',
                            formatter: (cell) => cell.getValue(),
                            field: 'line',
                            hozAlign: 'right',
                            formatter: function (cell) {
                                var data = cell.getData(),
                                    output = data.line;

                                if (data.editorLink.trim().length > 0) {
                                    output += makeEditorLink(data);
                                }

                                if (output === 0) {
                                    return '&mdash;';
                                }

                                return output;
                            },
                        },
                        cumulativeMemoryUsage: {
                            title: 'Memory Usage',
                            formatter: function (cell) {
                                var data = cell.getData();

                                if (!data.isNodeObject) {
                                    return '';
                                }

                                return formatBytes(data.cumulativeMemorySamples);
                            },
                            field: 'cumulativeMemorySamples',
                            hozAlign: 'right',
                        },
                        executionCount: {
                            title: 'Execution',
                            field: 'executionCount',
                            hozAlign: 'right',
                            formatter: function (cell) {
                                var data = cell.getData(),
                                    display = '';

                                if (!data.isNodeObject) {
                                    return '';
                                }

                                if (data.isHot) {
                                    display += `<span title="This code, or code it contains, is executed a large number of times.">${svgs.hotCode}</span>`;
                                }

                                display += data.executionCount;

                                return display;
                            },
                        },
                        totalExecutionTime: {
                            title: 'Total Time',
                            field: 'clientTotalTime',
                            formatter: function (cell) {
                                var data = cell.getData();

                                if (!data.isNodeObject) {
                                    return '';
                                }

                                return data.clientTotalTimeDisplay;
                            },
                            hozAlign: 'right',
                        },
                        selfExecutionTime: {
                            title: 'Tag Time',
                            headerHozAlign: 'right',
                            field: 'clientSelfTime',
                            formatter: function (cell) {
                                var data = cell.getData();

                                if (!data.isNodeObject) {
                                    return '';
                                }

                                return data.clientSelfTimeDisplay;
                            },
                            hozAlign: 'right',
                        },
                        percentOfExecutionTime: {
                            title: '%',
                            field: 'percentOfExecutionTime',
                            hozAlign: 'right',
                            headerHozAlign: 'right',
                            formatter: function (cell) {
                                var data = cell.getData(),
                                    contents = '';

                                if (!data.isNodeObject) {
                                    return contents;
                                } else {
                                    if (data.percentOfExecutionTime > 40) {
                                        contents += `<span title="This block takes up a disproportionate amount of total execution time.">${svgs.warning}</span>`;
                                    }
                                }

                                contents += data.percentOfExecutionTime;

                                return contents;
                            },
                        },
                    };

                    var tableOptions = {
                        dataTree: true,
                        dataTreeStartExpanded: true,
                        dataTreeChildField: 'children',
                        responsiveLayout: true,
                        tooltips: true,
                        rowFormatter: function (row) {
                            var data = row.getData(),
                                rowEl = row.getElement(); // Get the row element

                            if (data.isHot) {
                                rowEl.style.backgroundColor = colors.hotCode;
                            }

                            // If we replace the "hot" background color, that is fine.
                            // The execution time visibility is more important. The
                            // > 70 limit here is just to reduce the visual noise.
                            if (data.isNodeObject && data.totalElapsedTime != null && data.totalElapsedTime > 70) {
                                rowEl.style.backgroundColor = categoryColorMapping[data.executionTimeCategory];
                            }
                        },
                    };

                    var callTable = new Tabulator('#antlers-trace-call-table', {
                        ...tableOptions,
                        columns: [
                            columnConfig.expander,
                            columnConfig.sequence,
                            columnConfig.type,
                            columnConfig.item,
                            columnConfig.path,
                            columnConfig.lineNumber,
                            columnConfig.cumulativeMemoryUsage,
                            columnConfig.executionCount,
                            columnConfig.selfExecutionTime,
                            columnConfig.totalExecutionTime,
                            columnConfig.percentOfExecutionTime,
                        ],
                        data: data.data,
                    });

                    callTable.on('rowClick', function (e, row) {
                        openPropertiesWindow(row.getData());
                    });

                    btnCallGraph.addEventListener('click', function () {
                        tabCallGraph.style.display = 'block';
                        tabSourceGraph.style.display = 'none';

                        btnCallGraph.classList.add('antlers-trace-button-active');
                        btnNodeGraph.classList.remove('antlers-trace-button-active');
                        btnSourceGraph.classList.remove('antlers-trace-button-active');

                        callTable.replaceData(data.data);
                        callTable.hideColumn('path');
                    });

                    btnNodeGraph.addEventListener('click', function () {
                        tabCallGraph.style.display = 'block';
                        tabSourceGraph.style.display = 'none';

                        btnCallGraph.classList.remove('antlers-trace-button-active');
                        btnNodeGraph.classList.add('antlers-trace-button-active');
                        btnSourceGraph.classList.remove('antlers-trace-button-active');

                        callTable.replaceData(data.performance_items);
                        callTable.showColumn('path');
                    });

                    btnSourceGraph.addEventListener('click', function () {
                        tabCallGraph.style.display = 'none';
                        tabSourceGraph.style.display = 'block';

                        btnCallGraph.classList.remove('antlers-trace-button-active');
                        btnNodeGraph.classList.remove('antlers-trace-button-active');
                        btnSourceGraph.classList.add('antlers-trace-button-active');
                    });

                    var observer = new MutationObserver(() => {
                        matchHeights();
                    });

                    matchHeights();

                    var observerConfig = { attributes: true, attributeFilter: ['style'] };
                    observer.observe(debugBarBody, observerConfig);

                    addMetric('Expressions Processed', data.total_antlers_nodes.toLocaleString());
                }.bind(this),
            );
        },
    }));
})(PhpDebugBar.$);
