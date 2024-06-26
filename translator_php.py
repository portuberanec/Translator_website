from googletrans import *
from config import host, user, password, db_name, bot_token
import pymysql


try:
    connection = pymysql.connect(
        host=host,
        port=3306,
        user=user,
        password = password,
        database=db_name,
        cursorclass=pymysql.cursors.DictCursor
        )
    print("Connected to base")
                     #тело переводчика
    translator = Translator()

    messagelist = []
    MessageFile = open('C:\OSPanel\domains\Translatorwebsite\Stub.txt', encoding='utf-8')
    for line in MessageFile:
        messagelist.append(line)
    
    messagetext = messagelist[0].replace('\n', '')
    print (messagelist)
    print (messagetext)

    Str_jp = messagetext                        #Начальная строка, Отправляется в таблицу фраз в поле Phrase Jp
    
    if False: #detect(Str_jp) != 'ja'
        print(detect(Str_jp))
           #отправить в php сообщение о том, что строка не на японском
    else:
        Str_jp = Str_jp.replace(",", "")
        with connection.cursor() as cursor: #проверяет существование введённой строки в базе
            sql_quarry = "SELECT * FROM `Phrase` WHERE Phrase_Jp='{0}';".format(Str_jp)
            cursor.execute(sql_quarry)
            n = cursor.rowcount
            cursor.close()
            print(n)
           
        if (n == 0):
            #Если фразы нет в базе, он начинает переводить
            Str_rus_tr = translator.translate (Str_jp, dest='ru', src ='ja') #переведённая строка в виде транлятора
            Str_rus = Str_rus_tr.text #переведённая строка, отправляется в таблицу фраз в поле Phrase Rus

            Str_tr_jp_tr = translator.translate (Str_rus, dest='ja', src ='ru')
            Str_tr_jp = Str_tr_jp_tr.pronunciation #транслит начальной фразы, отправляется в таблицу фраз в поле Phrase Translit
   
            Str_rus_spl = Str_rus.split() #Список разделённых слов на русском, отправляется в таблицу слов в поле (Word_rus)
   
            words = []
            transliter = []
            for word in Str_rus_spl:
                g = (translator.translate(word, dest='ja', src = 'ru'))
                words.append(g.text) #Список слов, отправляется в таблицу слов в поле (Word_Jp)
                transliter.append(g.pronunciation) #Список произношений, отправляется в таблицу слов в поле (Word_Translit)

            Str_tr_jp = Str_tr_jp.replace("'", "")

            #Запись строки в базу
            with connection.cursor() as cursor: 
                sql_quarry = "INSERT INTO `Phrase` (`ID`, `Phrase_Jp`, `Phrase_Translit`, `Phrase_Rus`) VALUES (NULL, '{0}', '{1}', '{2}');".format(Str_jp, Str_tr_jp, Str_rus)
                cursor.execute(sql_quarry)
                connection.commit()
                print("phrase is commited") #Заменить на сообщение бота, пока не стану
                cursor.close()
        else:
            with connection.cursor() as cursor: 
                sql_quarry = "SELECT Phrase_Rus FROM `Phrase` WHERE Phrase_Jp='{0}';".format(Str_jp)
                cursor.execute(sql_quarry)
                fail = cursor.fetchone()
                cursor.close()
              #отправить в php сообщение, что фраза уже записана

            #Запись слов в базу
        with connection.cursor() as cursor: #берёт id фразы из базы
            sql_quarry = "SELECT ID FROM `Phrase` WHERE Phrase_Jp = '{0}';".format(Str_jp)
            cursor.execute(sql_quarry)
            row = cursor.fetchone()
            cursor.close()
        for i in range (0, len(words)):
            with connection.cursor() as cursor: #проверяет существование слова в базе
                sql_quarry = "SELECT * FROM `Words` WHERE Word_Jp = '{0}'".format(words[i])
                cursor.execute(sql_quarry)
                rowid = cursor.fetchone()
                n = cursor.rowcount
                cursor.close()
            if (n == 0):
                transliter[i] = transliter[i].replace("'", "")
                with connection.cursor() as cursor: #запись слов в базу с id строки, из которой пришли
                    sql_quarry = "INSERT INTO `Words` (`ID`, `Word_Jp`, `Word_Translit`, `Word_Rus`, `Phrase_ID`) VALUES (NULL, '{0}', '{1}', '{2}', '{3}');".format(words[i], transliter[i], Str_rus_spl[i], row['ID'])
                    cursor.execute(sql_quarry)
                    connection.commit()
                    print("word[{}] is commited".format(words[i])) #заменить на сообщение бота
                    cursor.close()
                with connection.cursor() as cursor:
                    sql_quarry = "SELECT ID FROM `Words` WHERE Word_Jp = '{0}';".format(words[i])
                    cursor.execute(sql_quarry)
                    rowid = cursor.fetchone()
                    cursor.close()
                with connection.cursor() as cursor: #Запись слова и фразы в дополнительную таблицу
                    sql_quarry = "INSERT INTO `Bind(phrase-word)` (`ID`, `Phrase_ID`, `Word_ID`, `UserID`) VALUES (NULL, '{0}', '{1}', '{2}');".format(row['ID'], rowid['ID'], messagelist[1])
                    cursor.execute(sql_quarry)
                    connection.commit()
                    cursor.close()
            else:
                print("The word[{}] exists in base".format(words[i])) #заменить на сообщение бота

                with connection.cursor() as cursor:
                    sql_quarry = "SELECT Phrase_ID FROM `Bind(phrase-word)` WHERE Word_ID = '{0}';".format(rowid['ID'])
                    cursor.execute(sql_quarry)
                    be_phrases = cursor.fetchall()
                    k = cursor.rowcount
                    cursor.close()

                for be_phrase in be_phrases:
                    with connection.cursor() as cursor:
                        sql_quarry = "SELECT Phrase_Jp, Phrase_Rus FROM `Phrase` WHERE ID = '{0}';".format(be_phrase['Phrase_ID'])
                        cursor.execute(sql_quarry)
                        be_word = cursor.fetchone()
                        cursor.close()
                    
                      #Bot.send_message(message.chat.id, text='Слово {0} существует во фразе {1} - {2}'.format(words[i], be_word['Phrase_Jp'], be_word['Phrase_Rus']))     заменить на сообщение о том, что слово существует во фразе

                with connection.cursor() as cursor: #Привязка существующего слова к новой фразе
                    sql_quarry = "INSERT INTO `Bind(phrase-word)` (`ID`, `Phrase_ID`, `Word_ID`, `UserID`) VALUES (NULL, '{0}', '{1}', '{2}');".format(row['ID'], rowid['ID'], messagelist[1])
                    cursor.execute(sql_quarry)
                    connection.commit()
                    cursor.close()
                    

          #  with connection.cursor() as cursor: #Вывод транслита и перевода фразы после записи
            #    sql_quarry = "SELECT * FROM `Phrase` WHERE Phrase_Jp = '{0}'".format(Str_jp)
            #    cursor.execute(sql_quarry)
            #    rows = cursor.fetchall()
             #   for row in rows:
                    #Bot.send_message(message.chat.id, text='{0} - {1}'.format(row['Phrase_Translit'], row['Phrase_Rus']), reply_markup=markup) отрапортовать о верно записанной фразе
                    #with open(r'C:\Users\portu\OneDrive\Рабочий стол\python\Универ\Telegram_bot\1.mp3', 'rb') as audio: #отправить в конфиг
                        #Bot.send_audio(message.from_user.id, audio)  отправить аудиофайл в php
               
except Exception as ex:
    print ("Connection to base failed")
    print(ex)
