"""Race harness: serves a captured page with one script routed through a delayed redirect.

GET /page/<name>?d=<script id>&s=<secs> -> <name>.html with that script routed via /delay (default wpforms-modern-js, 3 s)
GET /delay?u=<url>&s=3  -> sleeps s seconds, then 302 to the real URL
"""
import http.server
import re
import time
import urllib.parse

DELAYED_ID = 'wpforms-modern-js'


class Handler(http.server.BaseHTTPRequestHandler):
    def do_GET(self):
        parsed = urllib.parse.urlparse(self.path)
        if parsed.path.startswith('/page/'):
            name = parsed.path.split('/')[-1]
            q = urllib.parse.parse_qs(parsed.query)
            delayed_id = q.get('d', [DELAYED_ID])[0]
            secs = q.get('s', ['3'])[0]
            html = open(f'{name}.html', encoding='utf-8').read()
            html = re.sub(
                r'(<script\b[^>]*id=["\']' + re.escape(delayed_id) + r'["\'][^>]*src=["\'])([^"\']+)',
                lambda m: m.group(1) + '/delay?s=' + secs + '&u=' + urllib.parse.quote(m.group(2), safe=''),
                html,
            )
            body = html.encode('utf-8')
            self.send_response(200)
            self.send_header('Content-Type', 'text/html; charset=utf-8')
            self.send_header('Cache-Control', 'no-store')
            self.end_headers()
            self.wfile.write(body)
        elif parsed.path == '/delay':
            q = urllib.parse.parse_qs(parsed.query)
            time.sleep(float(q.get('s', ['3'])[0]))
            self.send_response(302)
            self.send_header('Location', q['u'][0])
            self.send_header('Cache-Control', 'no-store')
            self.end_headers()
        else:
            self.send_response(404)
            self.end_headers()

    def log_message(self, *args):
        pass


http.server.ThreadingHTTPServer(('0.0.0.0', 8765), Handler).serve_forever()
