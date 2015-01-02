#!/usr/bin/env python
# -*- coding: utf-8 -*-

# Drupal Planet Links Archive
# 
# Parses all .eml files in "mails" folder, and insert them in the
# database, following the structure of the ones included via
# cron or manually.
# 
# 
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
# 

import os
import email
import hashlib
import MySQLdb as mdb
from datetime import datetime

from dateutil.parser import parse

conn = mdb.connect('yourdbserverhere', 'yourusernamehere', 'yourpasswordhere', 'yourdatabasehere')
cursor = conn.cursor()

for k in os.listdir('mails'):
  print k
  if k.endswith('.eml'):
    f = open('mails/' + k, 'r')
    msg = email.message_from_file(f)
    f.close()

    parser = email.parser.HeaderParser()
    headers = parser.parsestr(msg.as_string())

    item_title =  headers['Subject']
    item_date = parse(headers['Date']).strftime("%Y-%m-%d %H:%M:%S")
    item_url = headers['Content-Base']
    item_id = hashlib.md5(item_title).hexdigest()

    query = "INSERT INTO rssingest(item_id,feed_url,item_title,item_date,item_url,fetch_date) " \
        "VALUES(%s,%s,%s,%s,%s,%s)"
    args = (item_id,'https://www.drupal.org/planet/rss.xml',item_title,item_date,item_url,datetime.now())
    cursor.execute(query, args)
    conn.commit()

