#!/bin/bash

soffice --headless --convert-to pdf:writer_pdf_Export -outdir "$1" "$2"
