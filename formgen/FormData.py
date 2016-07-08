# -*- coding: utf8 -*-
__author__ = 'Aleksandrov Oleg, 4231'

from PDFGenDAOPostgres import PDFGenDAOPostgres


class FormData:
    __dao = PDFGenDAOPostgres()
    __qs = []
    __small_qr = []
    __version = "0.1"
    __id_user = "null"
    __id_owner = "null"
    __id_premise = "null"
    __id_meeting = "null"
    __fio = "______________________"
    __phoneNumber = "______________________"
    __city = '__________'
    __street = '___________'
    __houseNumb = '_____'
    __apartment = '_______'
    __form = '_____________'
    __share = '____________'
    __formDate = '_________'
    __propertyS = '___________'

    def __init__(self, id_user, id_meeting):
        self.__id_meeting = str(id_meeting)
        self.__id_user = str(id_user)
        qs_small_qr = self.__dao.get_question(id_meeting)

        for value in qs_small_qr:
            self.__small_qr.append('s' + str(value[0]))
            self.__qs.append(value[1])

        if str(self.__dao.check_premise(self.__id_user)[0][0]) != 'None':
            result = self.__dao.get_title(id_meeting, id_user)
            self.__fio = result[0][2] + " " + result[0][0] + " " + result[0][1]
            self.__city = result[0][3]
            self.__street = result[0][4]
            self.__houseNumb = result[0][5]
            self.__apartment = str(result[0][6]) + ' ' + str(result[0][7])
            self.__form = str(result[0][8])
            self.__share = str(result[0][9] * 100 / result[0][10]) + '%'
            self.__formDate = str(result[0][11])
            self.__propertyS = str(result[0][12])

    def get_date(self):
        return {
            "fio": self.__fio,
            "city": self.__city,
            "street": self.__street,
            "houseNumb": self.__houseNumb,
            "apartment": self.__apartment,
            "phoneNumber": self.__phoneNumber,
            "formSeries": self.__form,
            "formDateOfIssue": self.__formDate,
            "propertyS": self.__propertyS,
            "share": self.__share
        }

    # версия| id_user | id_owner| id_premise | id meeting | количество страниц| и номер текущей|
    def get_big_qr_code_date(self):
        return 'b' + self.__version.ljust(10, ' ') + '|' \
               + self.__id_user.ljust(10, ' ') + '|' \
               + self.__id_owner.ljust(10, ' ') + '|' \
               + self.__id_premise.ljust(10, ' ') + '|' \
               + self.__id_meeting.ljust(10, ' ')

    def get_questions(self):
        return self.__qs

    def get_small_qr_code_date(self):
        return self.__small_qr
