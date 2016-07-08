
import bottle

from bottle import *
from PdfGen import PdfGen


bottle.debug(True)

app = bottle.app()

@get('/') # or @route('/login')
def login():
    return '''
        <form action="/" method="post">
            <p>UserID: <input name="p1" type="text" /></p>
            <p>MeetingID: <input name="p2" type="text" /></p>
            <input value="Send" type="submit" />
        </form>
    '''

@post('/') # or @route('/login', method='POST')
def do_login():
    pg = PdfGen()
    p1 = request.forms.get('p1')
    p2 = request.forms.get('p2')
    res = pg.execute(int(p1), int(p2))
    return bottle.static_file(res[0], res[1])



# #  Web application main  # #

def main():
    sys.path.append(os.path.dirname(os.path.abspath(__file__)))
    os.chdir(os.path.dirname(os.path.abspath(__file__)))
    cwd = os.getcwd()
    print(cwd)

    # Start the Bottle webapp
    bottle.debug(True)
    bottle.default_app()
    # bottle.run(host='localhost', port=8080, app=app, quiet=False, reloader=True)
    print("Starting bottle...")
    bottle.run(host='localhost', port=8080, app=app, quiet=False)

if __name__ == "__main__":
    main()
