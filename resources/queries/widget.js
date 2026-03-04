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

        runSelect(element, statement, rows) {
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
                    td.textContent = row[key];
                    tr.append(td);
                }
                values.push(tr);
            }

            const table = document.createElement('table');
            table.classList.add(csscls('result'));
            const thead = document.createElement('thead');
            const tbody = document.createElement('tbody');
            const headerRow = document.createElement('tr');
            headerRow.append(...headings);
            thead.append(headerRow);
            tbody.append(...values);
            table.append(thead, tbody);

            element.append(table);
        }

        explainMysql(element, statement, rows, visual) {
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
                    td.textContent = row[key];
                    tr.append(td);
                }
                values.push(tr);
            }

            const table = document.createElement('table');
            table.classList.add(csscls('explain'));
            const thead = document.createElement('thead');
            const tbody = document.createElement('tbody');
            const headerRow = document.createElement('tr');
            headerRow.append(...headings);
            thead.append(headerRow);
            tbody.append(...values);
            table.append(thead, tbody);

            element.append(table);
            if (visual) {
                element.append(this.explainVisual(statement, visual.confirm));
            }
        }

        explainPgsql(element, statement, rows, visual) {
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
            const tbody = document.createElement('tbody');
            tbody.append(...values);
            table.append(tbody);

            element.append(table);
            if (visual) {
                element.append(this.explainVisual(statement, visual.confirm));
            }
        }

        explainVisual(statement, confirmMessage) {
            const explainLink = document.createElement('a');
            explainLink.href = '#';
            explainLink.target = '_blank';
            explainLink.rel = 'noopener';
            explainLink.classList.add(csscls('visual-link'));

            const explainButton = document.createElement('a');
            explainButton.textContent = 'Visual Explain';
            explainButton.classList.add(csscls('visual-explain'));
            explainButton.addEventListener('click', () => {
                if (!confirm(confirmMessage)) // eslint-disable-line no-alert
                    return;
                fetch(statement.explain.url, {
                    method: 'POST',
                    body: JSON.stringify({
                        connection: statement.explain.connection,
                        query: statement.explain.query,
                        bindings: statement.params,
                        hash: statement.explain.hash,
                        mode: 'visual'
                    })
                }).then((response) => {
                    response.json()
                        .then((json) => {
                            if (!response.ok)
                                return alert(json.message); // eslint-disable-line no-alert
                            explainLink.href = json.data;
                            explainLink.textContent = json.data;
                            window.open(json.data, '_blank', 'noopener');
                        })
                        .catch(err => alert(`Response body could not be parsed. (${err})`)); // eslint-disable-line no-alert
                }).catch((e) => {
                    alert(e.message); // eslint-disable-line no-alert
                });
            });

            const div = document.createElement('div');
            div.append(explainButton, explainLink);
            return div;
        }

        itemRenderer(li, stmt, filters) {
            // Call parent's item renderer first
            super.itemRenderer(li, stmt, filters);

            // Add explain button if available
            if (stmt.explain) {
                let table = li.querySelector(`.${csscls('params')}`);
                table.style.display = '';

                this.renderDetailExplain(table, 'Result', stmt, this.runSelect.bind(this), 'RUN', 'result');
                if (table && ['mariadb', 'mysql'].includes(stmt.explain.driver)) {
                    this.renderDetailExplain(table, 'Performance', stmt, this.explainMysql.bind(this), 'EXPLAIN');
                } else if (table && stmt.explain.driver === 'pgsql') {
                    this.renderDetailExplain(table, 'Performance', stmt, this.explainPgsql.bind(this), 'EXPLAIN');
                }
            }
        }

        renderDetailExplain(table, caption, statement, explainFn, btnTitle, mode = 'explain') {
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

            const btn = document.createElement('button');
            btn.textContent = 'Run ' + btnTitle;
            btn.classList.add(csscls('explain-btn'));
            td.append(btn);

            bodyTr.append(td);
            tbody.append(bodyTr);
            table.append(tbody);

            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                fetch(statement.explain.url, {
                    method: 'POST',
                    body: JSON.stringify({
                        connection: statement.explain.connection,
                        query: statement.explain.query,
                        bindings: statement.params,
                        hash: statement.explain.hash,
                        mode: mode
                    })
                }).then((response) => {
                    response.json()
                        .then((json) => {
                            if (!response.ok)
                                return alert(json.message); // eslint-disable-line no-alert
                            td.innerHTML = '';
                            explainFn(td, statement, json.data, json.visual);
                        })
                        .catch(err => alert(`Response body could not be parsed. (${err})`)); // eslint-disable-line no-alert
                }).catch((e) => {
                    alert(e.message); // eslint-disable-line no-alert
                });
            });
        }
    }

    PhpDebugBar.Widgets.LaravelQueriesWidget = LaravelQueriesWidget;
})();
