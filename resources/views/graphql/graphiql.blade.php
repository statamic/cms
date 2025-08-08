<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GraphiQL ‹ Statamic</title>
    <style>
      body {
        margin: 0;
        overflow: hidden; /* in Firefox */
      }

      #graphiql {
        height: 100dvh;
      }

      .loading {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
      }
    </style>
    <link
      rel="stylesheet"
      href="https://esm.sh/graphiql@4.0.0/dist/style.css"
    />
    <link
      rel="stylesheet"
      href="https://esm.sh/@graphiql/plugin-explorer@4.0.0/dist/style.css"
    />
    <!-- Note: the ?standalone flag bundles the module along with all of its `dependencies`, excluding `peerDependencies`, into a single JavaScript file. -->
    <script type="importmap">
      {
        "imports": {
          "react": "https://esm.sh/react@19.1.0",
          "react/jsx-runtime": "https://esm.sh/react@19.1.0/jsx-runtime",

          "react-dom": "https://esm.sh/react-dom@19.1.0",
          "react-dom/client": "https://esm.sh/react-dom@19.1.0/client",

          "graphiql": "https://esm.sh/graphiql@4.0.0?standalone&external=react,react/jsx-runtime,react-dom,@graphiql/react",
          "@graphiql/plugin-explorer": "https://esm.sh/@graphiql/plugin-explorer@4.0.0?standalone&external=react,react/jsx-runtime,react-dom,@graphiql/react,graphql",
          "@graphiql/react": "https://esm.sh/@graphiql/react@0.30.0?standalone&external=react,react/jsx-runtime,react-dom,graphql,@graphiql/toolkit",

          "@graphiql/toolkit": "https://esm.sh/@graphiql/toolkit@0.11.2?standalone&external=graphql",
          "graphql": "https://esm.sh/graphql@16.11.0"
        }
      }
    </script>
    <script type="module">
      // Import React and ReactDOM
      import React from 'react';
      import ReactDOM from 'react-dom/client';
      // Import GraphiQL and the Explorer plugin
      import { GraphiQL } from 'graphiql';
      import { createGraphiQLFetcher } from '@graphiql/toolkit';
      import { explorerPlugin } from '@graphiql/plugin-explorer';

      var xcsrfToken = null;

      const fetcher = createGraphiQLFetcher({
        url: '{{ $url }}',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'x-csrf-token': xcsrfToken || '{{ csrf_token() }}',
        },
        fetch: async (fetchURL, fetchOptions) => {
            return await fetch(fetchURL, fetchOptions).then((response) => {
                xcsrfToken = response.headers.get('x-csrf-token');
                return response;
            });
        },
      });
      
      const explorer = explorerPlugin();

      function App() {
        return React.createElement(GraphiQL, {
          fetcher,
          plugins: [explorer],
        });
      }

      const container = document.getElementById('graphiql');
      const root = ReactDOM.createRoot(container);
      root.render(React.createElement(App));
    </script>
  </head>
  <body>
    <div id="graphiql">
      <div class="loading">Loading…</div>
    </div>
  </body>
</html>