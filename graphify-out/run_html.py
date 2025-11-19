import sys
from graphify.cli import app
try:
    app(args=["export", "html"], prog_name="graphify")
except SystemExit:
    pass
