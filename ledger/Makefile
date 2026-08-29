CC = gcc
CFLAGS = -shared -fPIC -O2
LDFLAGS = -lcrypto
TARGET = ledger_crypto.so
SRC = ledger_crypto.c

all: $(TARGET)

$(TARGET): $(SRC)
	$(CC) $(CFLAGS) -o $@ $< $(LDFLAGS)

clean:
	rm -f $(TARGET)

install: $(TARGET)
	@echo "Built $(TARGET) — place in Edunex/ledger/"

.PHONY: all clean install
