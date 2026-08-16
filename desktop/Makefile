# EDUNEX desktop client (ncurses + libcurl + json-c)
CC      ?= cc
CFLAGS  ?= -O2 -Wall -Wextra
CFLAGS  += $(shell pkg-config --cflags ncursesw libcurl json-c)
LIBS    := $(shell pkg-config --libs ncursesw libcurl json-c) -lpthread

TARGET  := edunex-cli
SRCS    := src/main.c src/api.c src/ui.c src/screens.c
OBJS    := $(SRCS:.c=.o)

all: $(TARGET)

$(TARGET): $(OBJS)
	$(CC) $(CFLAGS) -o $@ $(OBJS) $(LIBS)

%.o: %.c src/api.h src/ui.h src/screens.h
	$(CC) $(CFLAGS) -c -o $@ $<

clean:
	rm -f $(OBJS) $(TARGET)

install: $(TARGET)
	install -m 755 $(TARGET) /usr/local/bin/$(TARGET)

.PHONY: all clean install
