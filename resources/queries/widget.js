(function () {
    const csscls = PhpDebugBar.utils.makecsscls('phpdebugbar-widgets-');

    /**
     * Widget for displaying sql queries with Laravel-specific features.
     * Extends the base SQLQueriesWidget to add EXPLAIN functionality.
     *
     * Options:
     *  - data
     */
    class LaravelQueriesWidget extends PhpDebugBar.Widgets.SQLQueriesWidget {

        buildTable(rows, opts = {}) {
            const headings = [];
            for (const key in rows[0]) {
                const th = document.createElement('th');
                th.textContent = key;
                headings.push(th);
            }

            const values = [];
            for (const row of rows) {
                const tr = document.createElement('tr');
                for (const key in row) {
                    const td = document.createElement('td');
                    const text = row[key] == null ? '' : String(row[key]);
                    td.textContent = text;
                    if (!opts.expanded) {
                        td.title = text;
                        td.addEventListener('click', (e) => {
                            e.stopPropagation();
                            td.classList.toggle(csscls('cell-expanded'));
                            if (td.classList.contains(csscls('cell-expanded'))) {
                                const selection = window.getSelection();
                                const range = document.createRange();
                                range.selectNodeContents(td);
                                selection.removeAllRanges();
                                selection.addRange(range);
                            }
                        });
                    }
                    tr.append(td);
                }
                values.push(tr);
            }

            const table = document.createElement('table');
            table.classList.add(csscls('explain'));
            if (opts.expanded) {
                table.classList.add(csscls('explain-full'));
            }
            const thead = document.createElement('thead');
            const tbody = document.createElement('tbody');
            const headerRow = document.createElement('tr');
            headerRow.append(...headings);
            thead.append(headerRow);
            tbody.append(...values);
            table.append(thead, tbody);
            return table;
        }

        buildPgsqlTable(rows, opts = {}) {
            const values = [];
            for (const row of rows) {
                const tr = document.createElement('tr');
                const td = document.createElement('td');
                td.textContent = row;
                tr.append(td);
                values.push(tr);
            }

            const table = document.createElement('table');
            table.classList.add(csscls('explain'));
            if (opts.expanded) {
                table.classList.add(csscls('explain-full'));
            }
            const tbody = document.createElement('tbody');
            tbody.append(...values);
            table.append(tbody);
            return table;
        }

        actionButton(label, onClick) {
            const btn = document.createElement('button');
            btn.textContent = label;
            btn.classList.add(csscls('explain-btn'));
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                onClick(e);
            });
            return btn;
        }

        fetchQuery(statement, mode, format) {
            const body = {
                connection: statement.explain.connection,
                query: statement.explain.query,
                bindings: statement.params,
                hash: statement.explain.hash,
                mode: mode,
            };
            if (format) {
                body.format = format;
            }
            return fetch(statement.explain.url, {
                method: 'POST',
                body: JSON.stringify(body),
            }).then((response) =>
                response.json().then((json) => {
                    if (!response.ok)
                        throw new Error(json.message || 'Request failed');
                    return json;
                })
            );
        }

        renderResult(container, statement, data, btnBar) {
            container.innerHTML = '';

            const result = data.result;
            if (Array.isArray(result) && result.length > 0 && typeof result[0] === 'object') {
                const wrapper = document.createElement('div');
                wrapper.classList.add(csscls('explain-scroll'));
                wrapper.append(this.buildTable(result));
                container.append(wrapper);
                btnBar.append(this.actionButton('Expand', () => {
                    this.showPopup(statement.explain.query, this.buildTable(result, { expanded: true }));
                }));
            } else {
                const empty = document.createElement('em');
                empty.textContent = 'No results';
                container.append(empty);
            }

            container.prepend(btnBar);
        }

        renderDump(container, statement, data, btnBar) {
            container.innerHTML = '';
            PhpDebugBar.Widgets.renderValueInto(container, data.result);
            PhpDebugBar.utils.sfDump(container);
            container.prepend(btnBar);
        }

        renderExplain(container, statement, data, driver, btnBar) {
            container.innerHTML = '';

            const rows = data;
            const buildFn = driver === 'pgsql' ? 'buildPgsqlTable' : 'buildTable';

            const wrapper = document.createElement('div');
            wrapper.classList.add(csscls('explain-scroll'));
            wrapper.append(this[buildFn](rows));
            container.append(wrapper);

            btnBar.append(this.actionButton('Expand', () => {
                this.showPopup(statement.explain.query, this[buildFn](rows, { expanded: true }));
            }));

            container.prepend(btnBar);
        }

        showPopup(query, contentEl) {
            const overlay = document.createElement('div');
            overlay.classList.add(csscls('explain-overlay'));

            const popup = document.createElement('div');
            popup.classList.add(csscls('explain-popup'));

            const header = document.createElement('div');
            header.classList.add(csscls('explain-popup-header'));

            const title = document.createElement('span');
            title.innerHTML = PhpDebugBar.Widgets.highlight(query.length > 120 ? query.substring(0, 120) + '...' : query, 'sql');
            title.classList.add(csscls('explain-popup-title'));

            const closeBtn = document.createElement('button');
            closeBtn.textContent = '\u2715';
            closeBtn.classList.add(csscls('explain-popup-close'));
            closeBtn.addEventListener('click', () => overlay.remove());

            header.append(title, closeBtn);

            const body = document.createElement('div');
            body.classList.add(csscls('explain-popup-body'));
            body.append(contentEl);

            popup.append(header, body);
            overlay.append(popup);

            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) overlay.remove();
            });

            document.addEventListener('keydown', function onEsc(e) {
                if (e.key === 'Escape') {
                    overlay.remove();
                    document.removeEventListener('keydown', onEsc);
                }
            });

            document.querySelector('div.phpdebugbar').append(overlay);
        }

        itemRenderer(li, stmt, filters) {
            // Call parent's item renderer first
            super.itemRenderer(li, stmt, filters);

            // Add explain button if available
            if (stmt.explain) {
                let table = li.querySelector(`.${csscls('params')}`);
                table.style.display = '';

                this.renderDetailSection(table, 'Result', stmt, 'result');
                this.renderDetailSection(table, 'Performance', stmt, 'explain');
            }
        }

        renderDetailSection(table, caption, statement, mode) {
            const thead = document.createElement('thead');
            const tr = document.createElement('tr');
            const th = document.createElement('th');
            th.colSpan = 2;
            th.classList.add(csscls('name'));
            th.innerHTML = caption;
            tr.append(th);
            thead.append(tr);
            table.append(thead);

            const tbody = document.createElement('tbody');
            const bodyTr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = 2;

            const driver = statement.explain.driver;

            if (mode === 'result') {
                const makeBtnBar = () => {
                    const bar = document.createElement('div');
                    bar.classList.add(csscls('explain-btnbar'));
                    bar.append(
                        this.actionButton('Run SELECT', () => {
                            const btnBar = makeBtnBar();
                            this.fetchQuery(statement, 'result').then((json) => {
                                this.renderResult(td, statement, json.data, btnBar);
                            }).catch((e) => alert(e.message)); // eslint-disable-line no-alert
                        }),
                        this.actionButton('Run SELECT (dump)', () => {
                            const btnBar = makeBtnBar();
                            this.fetchQuery(statement, 'result', 'dump').then((json) => {
                                this.renderDump(td, statement, json.data, btnBar);
                            }).catch((e) => alert(e.message)); // eslint-disable-line no-alert
                        })
                    );
                    return bar;
                };
                td.append(makeBtnBar());
            } else {
                const run = () => {
                    this.fetchQuery(statement, 'explain').then((json) => {
                        const btnBar = document.createElement('div');
                        btnBar.classList.add(csscls('explain-btnbar'));
                        btnBar.append(this.actionButton('Re-run EXPLAIN', run));

                        if (json.visual) {
                            btnBar.append(this.buildVisualExplainButton(statement, json.visual.confirm));
                        }

                        this.renderExplain(td, statement, json.data, driver, btnBar);
                    }).catch((e) => alert(e.message)); // eslint-disable-line no-alert
                };

                td.append(this.actionButton('Run EXPLAIN', run));
            }

            bodyTr.append(td);
            tbody.append(bodyTr);
            table.append(tbody);
        }

        buildVisualExplainButton(statement, confirmMessage) {
            const linkContainer = document.createElement('span');
            linkContainer.classList.add(csscls('visual-link'));

            const btn = this.actionButton('Visual Explain', () => {
                if (!confirm(confirmMessage)) // eslint-disable-line no-alert
                    return;
                this.fetchQuery(statement, 'visual').then((json) => {
                    linkContainer.innerHTML = '';
                    const link = document.createElement('a');
                    link.href = json.data;
                    link.textContent = json.data;
                    link.target = '_blank';
                    link.rel = 'noopener';
                    linkContainer.append(link);
                    window.open(json.data, '_blank', 'noopener');
                }).catch((e) => alert(e.message)); // eslint-disable-line no-alert
            });

            const wrapper = document.createDocumentFragment();
            wrapper.append(btn, linkContainer);
            return wrapper;
        }
    }

    PhpDebugBar.Widgets.LaravelQueriesWidget = LaravelQueriesWidget;
})();
